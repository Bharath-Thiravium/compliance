<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Services\Compliance\CsvNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/*
 | Table name map (wrong → correct)
 |   employees  → workforce_employee
 |   payroll    → workforce_payroll_entry   (requires payroll_cycle_id)
 |   attendance → workforce_attendance
 */

class DataInputController extends Controller
{
    public function saveManualData(Request $request, int $batchId)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $validated = $request->validate([
                'employees_data' => 'nullable|string',
                'payroll_data'   => 'nullable|string',
                'attendance_data'=> 'nullable|string',
            ]);

            $branchId = $this->resolveBatchBranchId($batch);
            $employees = $this->parseManualEmployees($validated['employees_data'] ?? '');
            $attendance = $this->parseManualAttendance($validated['attendance_data'] ?? '');
            $payroll = $this->parseManualPayroll($validated['payroll_data'] ?? '');

            $counts = DB::transaction(function () use ($batch, $branchId, $employees, $attendance, $payroll) {
                $employeeMaps = $this->upsertManualEmployees($employees, $batch->tenant_id, $branchId);
                $cycleId = $this->resolveManualPayrollCycle($batch);

                $this->replaceManualPayroll($payroll, $employeeMaps, $batch->tenant_id, $branchId, $cycleId);
                $this->replaceManualAttendance($attendance, $employeeMaps, $batch->tenant_id, $branchId, $batch);

                ComplianceBatchForm::where('batch_id', $batch->id)->update([
                    'status' => 'pending',
                    'file_path' => 'storage/forms/pending/placeholder.pdf',
                    'updated_at' => now(),
                ]);

                $batch->update([
                    'branch_id' => $branchId,
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);

                return [
                    'employees' => count($employeeMaps['ids']),
                    'payroll' => count($payroll),
                    'attendance' => count($attendance),
                ];
            });

            Log::info('Manual data persisted for batch', [
                'batch_id' => $batchId,
                'tenant_id' => $batch->tenant_id,
                'branch_id' => $branchId,
                'counts' => $counts,
            ]);

            try {
                $generationResult = $this->maybeGenerateForms($batch->fresh());
            } catch (\Throwable $generationError) {
                Log::error('Manual data generation trigger failed', [
                    'batch_id' => $batchId,
                    'error' => $generationError->getMessage(),
                ]);
                $generationResult = ['triggered' => false, 'reason' => $generationError->getMessage()];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Manual data saved for this tenant, branch, and period',
                'counts' => $counts,
                'generation' => $generationResult,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual data save error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function resolveBatchBranchId(ComplianceExecutionBatch $batch): int
    {
        if ($batch->branch_id) {
            $exists = DB::table('branches')
                ->where('tenant_id', $batch->tenant_id)
                ->where('id', $batch->branch_id)
                ->exists();

            if ($exists) {
                return (int) $batch->branch_id;
            }
        }

        $userBranchId = Auth::user()->branch_id ?? null;
        if ($userBranchId) {
            $exists = DB::table('branches')
                ->where('tenant_id', $batch->tenant_id)
                ->where('id', $userBranchId)
                ->exists();

            if ($exists) {
                return (int) $userBranchId;
            }
        }

        $branchId = DB::table('branches')
            ->where('tenant_id', $batch->tenant_id)
            ->orderBy('id')
            ->value('id');

        if (! $branchId) {
            $branchId = DB::table('branches')->insertGetId([
                'tenant_id' => $batch->tenant_id,
                'branch_name' => 'Main Branch',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $branchId;
    }

    private function parseManualEmployees(string $input): array
    {
        $rows = [];
        foreach ($this->manualLines($input) as $index => $line) {
            $cols = $this->manualColumns($line);
            if (count($cols) < 3) {
                throw new \InvalidArgumentException('Employees manual data format: use name, designation, salary or employee_code, name, designation, salary.');
            }

            $hasCode = count($cols) >= 4;
            $name = $hasCode ? $cols[1] : $cols[0];
            $rows[] = [
                'employee_code' => $hasCode ? $cols[0] : $this->manualEmployeeCode($name),
                'name' => $name,
                'designation' => $hasCode ? ($cols[2] ?? null) : ($cols[1] ?? null),
                'basic_salary' => $hasCode ? ($cols[3] ?? 0) : ($cols[2] ?? 0),
                'date_of_joining' => now()->toDateString(),
                'status' => 'active',
                '_line' => $index + 1,
            ];
        }

        if (empty($rows)) {
            throw new \InvalidArgumentException('Employees manual data is required.');
        }

        return $rows;
    }

    private function parseManualAttendance(string $input): array
    {
        $rows = [];
        foreach ($this->manualLines($input) as $index => $line) {
            $cols = $this->manualColumns($line);
            if (count($cols) < 3) {
                throw new \InvalidArgumentException('Attendance manual data format: use employee_code/name, present_days, absent_days.');
            }

            $present = CsvNormalizer::normalizeInt($cols[1] ?? null);
            $absent = CsvNormalizer::normalizeInt($cols[2] ?? null);
            $rows[] = [
                'employee_ref' => $cols[0],
                'present_days' => $present,
                'absent_days' => $absent,
                'working_days' => $present + $absent,
                '_line' => $index + 1,
            ];
        }

        if (empty($rows)) {
            throw new \InvalidArgumentException('Attendance manual data is required.');
        }

        return $rows;
    }

    private function parseManualPayroll(string $input): array
    {
        $rows = [];
        foreach ($this->manualLines($input) as $index => $line) {
            $cols = $this->manualColumns($line);
            if (count($cols) < 5) {
                throw new \InvalidArgumentException('Payroll manual data format: use employee_code/name, basic, hra, deductions, net_pay.');
            }

            $basic = CsvNormalizer::normalizeFloat($cols[1] ?? null);
            $hra = CsvNormalizer::normalizeFloat($cols[2] ?? null);
            $deductions = CsvNormalizer::normalizeFloat($cols[3] ?? null);
            $net = CsvNormalizer::normalizeFloat($cols[4] ?? null);
            $gross = max($basic + $hra, $net + $deductions);

            $rows[] = [
                'employee_ref' => $cols[0],
                'basic_earned' => $basic,
                'hra_earned' => $hra,
                'gross_salary' => $gross,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                '_line' => $index + 1,
            ];
        }

        if (empty($rows)) {
            throw new \InvalidArgumentException('Payroll manual data is required.');
        }

        return $rows;
    }

    private function manualLines(string $input): array
    {
        return array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($input)) ?: [],
            fn (string $line): bool => trim($line) !== ''
        ));
    }

    private function manualColumns(string $line): array
    {
        return array_map('trim', str_getcsv($line));
    }

    private function manualEmployeeCode(string $name): string
    {
        return 'MAN' . strtoupper(substr(hash('crc32b', strtolower(trim($name))), 0, 6));
    }

    private function upsertManualEmployees(array $rows, int $tenantId, int $branchId): array
    {
        $ids = [];
        $refs = [];

        foreach ($rows as $row) {
            $code = trim($row['employee_code']);
            $payload = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_code' => $code,
                'name' => $row['name'],
                'designation' => $row['designation'] ?? null,
                'date_of_joining' => $this->parseDate($row['date_of_joining'] ?? null) ?? now()->toDateString(),
                'basic_salary' => CsvNormalizer::normalizeFloat((string) ($row['basic_salary'] ?? 0)),
                'status' => $row['status'] ?? 'active',
                'updated_at' => now(),
            ];

            $existingId = DB::table('workforce_employee')
                ->where('tenant_id', $tenantId)
                ->where('employee_code', $code)
                ->value('id');

            if ($existingId) {
                DB::table('workforce_employee')->where('id', $existingId)->update(
                    array_diff_key($payload, ['tenant_id' => 1, 'employee_code' => 1])
                );
                $id = (int) $existingId;
            } else {
                $id = DB::table('workforce_employee')->insertGetId($payload + ['created_at' => now()]);
            }

            $ids[$code] = $id;
            $refs[$this->manualRefKey($code)] = $id;
            $refs[$this->manualRefKey($row['name'])] = $id;
        }

        return ['ids' => $ids, 'refs' => $refs];
    }

    private function resolveManualPayrollCycle(ComplianceExecutionBatch $batch): int
    {
        $periodFrom = $batch->period_from?->toDateString()
            ?? \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->startOfMonth()->toDateString();
        $periodTo = $batch->period_to?->toDateString()
            ?? \Carbon\Carbon::parse($periodFrom)->endOfMonth()->toDateString();

        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $batch->tenant_id)
            ->whereDate('period_from', $periodFrom)
            ->whereDate('period_to', $periodTo)
            ->value('id');

        if ($cycleId) {
            return (int) $cycleId;
        }

        return DB::table('workforce_payroll_cycle')->insertGetId([
            'tenant_id' => $batch->tenant_id,
            'cycle_name' => 'Manual Entry ' . \Carbon\Carbon::parse($periodFrom)->format('M Y'),
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'status' => 'processed',
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function replaceManualPayroll(array $rows, array $employeeMaps, int $tenantId, int $branchId, int $cycleId): void
    {
        $employeeIds = array_values($employeeMaps['ids']);
        DB::table('workforce_payroll_entry')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('payroll_cycle_id', $cycleId)
            ->whereIn('employee_id', $employeeIds)
            ->delete();

        foreach ($rows as $row) {
            $employeeId = $this->resolveManualEmployeeId($row['employee_ref'], $employeeMaps, 'payroll', $row['_line']);
            DB::table('workforce_payroll_entry')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'payroll_cycle_id' => $cycleId,
                'employee_id' => $employeeId,
                'total_days_worked' => 26,
                'paid_leave_days' => 0,
                'unpaid_leave_days' => 0,
                'overtime_hours' => 0,
                'basic_earned' => $row['basic_earned'],
                'da_earned' => 0,
                'hra_earned' => $row['hra_earned'],
                'other_allowances' => max(0, $row['gross_salary'] - $row['basic_earned'] - $row['hra_earned']),
                'overtime_wages' => 0,
                'gross_salary' => $row['gross_salary'],
                'pf_employee' => 0,
                'esi_employee' => 0,
                'professional_tax' => 0,
                'fines' => 0,
                'advances' => 0,
                'other_deductions' => $row['total_deductions'],
                'total_deductions' => $row['total_deductions'],
                'net_salary' => $row['net_salary'],
                'payment_date' => null,
                'payment_mode' => 'Manual Entry',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function replaceManualAttendance(array $rows, array $employeeMaps, int $tenantId, int $branchId, ComplianceExecutionBatch $batch): void
    {
        $periodStart = $batch->period_from
            ? \Carbon\Carbon::parse($batch->period_from)->startOfMonth()
            : \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->startOfMonth();
        $periodEnd = $batch->period_to
            ? \Carbon\Carbon::parse($batch->period_to)->endOfDay()
            : $periodStart->copy()->endOfMonth();
        $employeeIds = array_values($employeeMaps['ids']);

        DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereIn('employee_id', $employeeIds)
            ->delete();

        foreach ($rows as $row) {
            $employeeId = $this->resolveManualEmployeeId($row['employee_ref'], $employeeMaps, 'attendance', $row['_line']);
            $absentDays = $row['absent_days'];
            $absentFilled = 0;
            $daysInMonth = $periodStart->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $periodStart->copy()->day($day);
                if ($date->dayOfWeek === 0) {
                    continue;
                }

                $remainingDays = $daysInMonth - $day + 1;
                $isAbsent = ($absentFilled < $absentDays) && ($remainingDays <= ($absentDays - $absentFilled));
                if ($isAbsent) {
                    $absentFilled++;
                }

                DB::table('workforce_attendance')->insert([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $employeeId,
                    'attendance_date' => $date->toDateString(),
                    'status' => $isAbsent ? 'absent' : 'present',
                    'overtime_hours' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function resolveManualEmployeeId(string $ref, array $employeeMaps, string $dataset, int $line): int
    {
        $key = $this->manualRefKey($ref);
        if (isset($employeeMaps['refs'][$key])) {
            return (int) $employeeMaps['refs'][$key];
        }

        throw new \InvalidArgumentException("Manual {$dataset} line {$line}: employee not found in Employees data: {$ref}");
    }

    private function manualRefKey(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    public function uploadPdfForm(Request $request, int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batchId)
                ->firstOrFail();

            $validated = $request->validate([
                'file' => 'required|file|mimes:pdf|max:10240'
            ]);

            // Store PDF
            if (!Storage::disk('local')->exists('compliance/manual_uploads')) {
                Storage::disk('local')->makeDirectory('compliance/manual_uploads');
            }

            $file = $request->file('file');
            $fileName = "batch_{$batchId}_{$formCode}_" . time() . ".pdf";
            $filePath = $file->storeAs('compliance/manual_uploads', $fileName, 'local');

            // Update batch form with file path
            DB::table('compliance_batch_forms')
                ->where('batch_id', $batchId)
                ->where('form_code', $formCode)
                ->update([
                    'file_path' => $filePath,
                    'status' => 'generated',
                    'updated_at' => now()
                ]);

            Log::info("PDF uploaded for batch {$batchId}, form {$formCode}");

            return response()->json([
                'status' => 'success',
                'message' => 'PDF uploaded successfully',
                'file_path' => $filePath
            ]);
        } catch (\Exception $e) {
            Log::error('PDF upload error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function parseDate(?string $value): ?string
    {
        return CsvNormalizer::normalizeDate($value);
    }

    /**
     * Throw a clear error if required CSV headers are missing.
     */
    // ── Post-upload: trigger generation when all data is ready ─────────────

    private function maybeGenerateForms(\App\Models\ComplianceExecutionBatch $batch): array
    {
        // Only auto-generate for FULL subscription batches in pending state
        $tenant = DB::table('tenants')->where('id', $batch->tenant_id)->first();
        if (! $tenant || strtoupper($tenant->subscription_type) !== 'FULL') {
            return ['triggered' => false, 'reason' => 'MINIMAL subscription — click Proceed to Generate'];
        }

        if ($batch->status !== 'pending') {
            return ['triggered' => false, 'reason' => "Batch already in status: {$batch->status}"];
        }

        // Check all 3 datasets exist for this tenant/branch
        $dataEngine = app(\App\Services\Compliance\DataAvailabilityEngine::class);
        $availability = $dataEngine->checkDataAvailability(
            $batch->tenant_id,
            $batch->branch_id,
            $batch->period_month,
            $batch->period_year
        );

        // Core 3 datasets required — ignore optional ones (incidents, hazard, etc.)
        $coreReady = ! array_intersect(
            ['employees', 'attendance', 'payroll'],
            $availability['missing_data'] ?? []
        );

        if (! $coreReady) {
            $stillMissing = array_intersect(
                ['employees', 'attendance', 'payroll'],
                $availability['missing_data'] ?? []
            );
            return [
                'triggered'     => false,
                'reason'        => 'Waiting for: ' . implode(', ', $stillMissing),
                'data_summary'  => $availability['data_summary'],
            ];
        }

        // All core data present — mark processing and run generation
        Log::info('Auto-triggering form generation after CSV upload', [
            'batch_id'  => $batch->id,
            'tenant_id' => $batch->tenant_id,
        ]);

        $batch->update(['status' => 'processing']);

        try {
            $service = app(\App\Services\Compliance\RealtimeComplianceExecutionService::class);
            $results = $service->processBatchRealtime($batch->id, fn() => null);

            Log::info('Auto-generation complete', [
                'batch_id'   => $batch->id,
                'successful' => $results['successful'],
                'failed'     => $results['failed'],
                'audit'      => $results['audit'] ?? null,
            ]);

            return [
                'triggered'       => true,
                'generated_forms' => $results['successful'],
                'failed_forms'    => $results['failed'],
                'batch_status'    => $results['failed'] === 0 ? 'completed' : 'partial',
                'audit'           => $results['audit'] ?? null,
                'batch_score'     => $results['batch_score'] ?? null,
                'audit_status'    => $results['batch_status'] ?? null,
            ];

        } catch (\Throwable $e) {
            Log::error('Auto-generation failed', [
                'batch_id' => $batch->id,
                'error'    => $e->getMessage(),
            ]);

            $batch->update(['status' => 'pending']); // revert so user can retry

            return [
                'triggered' => false,
                'reason'    => 'Generation error: ' . $e->getMessage(),
            ];
        }
    }

    private function validateCsvHeaders(array $headers, array $required, string $type): void
    {
        $missing = array_diff($required, $headers);
        if (! empty($missing)) {
            throw new \InvalidArgumentException(
                "CSV ({$type}): missing required columns: " . implode(', ', $missing)
            );
        }
    }

    public function uploadCsvData(Request $request, int $batchId)
    {
        // ── 1. Table existence guard — never let a missing table produce a 500 ──
        $requiredTables = [
            'workforce_employee',
            'workforce_payroll_entry',
            'workforce_attendance',
            'workforce_payroll_cycle',
        ];
        $missingTables = array_filter(
            $requiredTables,
            fn($t) => ! \Illuminate\Support\Facades\Schema::hasTable($t)
        );
        if (! empty($missingTables)) {
            Log::error('CSV upload: required tables missing', ['tables' => $missingTables]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Required database tables missing: ' . implode(', ', $missingTables),
            ], 500);
        }

        // ── 2. Request validation ─────────────────────────────────────────────
        try {
            $validated = $request->validate([
                'file'         => 'required|file|max:10240',
                'dataset_type' => 'required|string|in:employees,payroll,attendance',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed: ' . implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        }

        // ── 3. Batch ownership check ──────────────────────────────────────────
        $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batchId)
            ->first();

        if (! $batch) {
            return response()->json([
                'status'  => 'error',
                'message' => "Batch #{$batchId} not found or access denied.",
            ], 404);
        }

        $file        = $request->file('file');
        $datasetType = $validated['dataset_type'];

        // ── 4. Parse CSV — BOM strip + delimiter detection ──────────────────
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not open uploaded file.',
            ], 500);
        }

        // Read first raw line to detect delimiter and strip UTF-8 BOM
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): file is empty or unreadable.",
            ], 422);
        }
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rawHeaders = fgetcsv($handle, 4096, $delimiter);
        if (! $rawHeaders) {
            fclose($handle);
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): file is empty or unreadable.",
            ], 422);
        }

        $headers = array_map(function (string $h): string {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // BOM on first cell
            return strtolower(trim(preg_replace('/\s+/', '_', $h)));
        }, $rawHeaders);

        // ── 5. Alias-aware header mapping + required-field validation ──────────
        $skippedColumns = [];
        $headerMapping  = \App\Services\Compliance\CsvColumnMapper::mapHeaders($rawHeaders, $datasetType, $skippedColumns);
        $requiredFields = \App\Services\Compliance\CsvColumnMapper::requiredFields($datasetType);
        $missing        = array_diff($requiredFields, array_keys($headerMapping));

        if (! empty($missing)) {
            fclose($handle);
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): missing required columns: " . implode(', ', $missing),
                'hint'    => 'Required: ' . implode(', ', $requiredFields),
            ], 422);
        }

        // ── 6. Parse all rows into memory using canonical field names ─────────
        $rows     = [];
        $colCount = count($rawHeaders);
        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if ($data === [null] || implode('', $data) === '') {
                continue;
            }
            if (count($data) < $colCount) {
                $data = array_pad($data, $colCount, '');
            } elseif (count($data) > $colCount) {
                $data = array_slice($data, 0, $colCount);
            }
            $row = \App\Services\Compliance\CsvColumnMapper::extractRow($data, $headerMapping);
            if (empty($row['employee_code'])) {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json([
                'status'  => 'error',
                'message' => "CSV ({$datasetType}): no valid data rows found after the header.",
            ], 422);
        }

        $branchId = $this->resolveBatchBranchId($batch);
        if ((int) ($batch->branch_id ?? 0) !== $branchId) {
            $batch->update(['branch_id' => $branchId, 'updated_at' => now()]);
            $batch->branch_id = $branchId;
        }

        Log::info('CSV upload started', [
            'batch_id'     => $batchId,
            'dataset_type' => $datasetType,
            'filename'     => $file->getClientOriginalName(),
            'row_count'    => count($rows),
            'tenant_id'    => $batch->tenant_id,
            'branch_id'    => $branchId,
        ]);

        // ── 7. Transaction — all inserts or nothing ───────────────────────────
        try {
            $recordsInserted = DB::transaction(function () use (
                $batch, $branchId, $datasetType, $rows
            ) {
                $inserted = 0;

                if ($datasetType === 'employees') {
                    foreach ($rows as $row) {
                        $exists = DB::table('workforce_employee')
                            ->where('tenant_id', $batch->tenant_id)
                            ->where('employee_code', $row['employee_code'])
                            ->exists();

                        $employeeFields = [
                                    'name'              => $row['name'],
                                    'father_name'       => $row['father_name']       ?? null,
                                    'gender'            => CsvNormalizer::normalizeGender($row['gender'] ?? null),
                                    'date_of_birth'     => $this->parseDate($row['date_of_birth'] ?? null),
                                    'marital_status'    => $row['marital_status']    ?? null,
                                    'nationality'       => $row['nationality']       ?? null,
                                    'mobile'            => CsvNormalizer::normalizeMobile($row['mobile'] ?? null),
                                    'email'             => $row['email']             ?? null,
                                    'permanent_address' => $row['permanent_address'] ?? null,
                                    'designation'       => $row['designation']       ?? null,
                                    'department'        => $row['department']        ?? null,
                                    'skill_type'        => $row['skill_type']        ?? null,
                                    'date_of_joining'   => $this->parseDate($row['date_of_joining'] ?? null),
                                    'pf_number'         => CsvNormalizer::normalizePF($row['pf_number'] ?? null),
                                    'esi_number'        => CsvNormalizer::normalizeESI($row['esi_number'] ?? null),
                                    'uan_number'        => CsvNormalizer::normalizeUAN($row['uan_number'] ?? $row['pf_number'] ?? null),
                                    'pan'               => $row['pan']               ?? null,
                                    'aadhaar'           => $row['aadhaar']           ?? null,
                                    'bank_account'      => $row['bank_account']      ?? null,
                                    'bank_name'         => $row['bank_name']         ?? null,
                                    'ifsc'              => $row['ifsc']              ?? null,
                                    'basic_salary'      => CsvNormalizer::normalizeFloat($row['basic_salary'] ?? null),
                                ];

                        if ($exists) {
                            DB::table('workforce_employee')
                                ->where('tenant_id', $batch->tenant_id)
                                ->where('employee_code', $row['employee_code'])
                                ->update(array_merge($employeeFields, ['updated_at' => now()]));
                        } else {
                            DB::table('workforce_employee')->insert(array_merge($employeeFields, [
                                'tenant_id'       => $batch->tenant_id,
                                'branch_id'       => $branchId,
                                'employee_code'   => $row['employee_code'],
                                'date_of_joining' => $this->parseDate($row['date_of_joining'] ?? null) ?? now()->toDateString(),
                                'status'          => 'active',
                                'created_at'      => now(),
                                'updated_at'      => now(),
                            ]));
                        }
                        $inserted++;
                    }

                } elseif ($datasetType === 'payroll') {
                    // Resolve or create payroll cycle once for the whole file
                    $cycleId = DB::table('workforce_payroll_cycle')
                        ->where('tenant_id', $batch->tenant_id)
                        ->whereDate('period_from', $batch->period_from->toDateString())
                        ->whereDate('period_to',   $batch->period_to->toDateString())
                        ->value('id');

                    if (! $cycleId) {
                        $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                            'tenant_id'    => $batch->tenant_id,
                            'cycle_name'   => 'CSV Import ' . $batch->period_from->format('M Y'),
                            'period_from'  => $batch->period_from->toDateString(),
                            'period_to'    => $batch->period_to->toDateString(),
                            'status'       => 'processed',
                            'processed_at' => now(),
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }

                    foreach ($rows as $row) {
                        $empCode    = $row['employee_code'];
                        $employeeId = DB::table('workforce_employee')
                            ->where('tenant_id', $batch->tenant_id)
                            ->where('employee_code', $empCode)
                            ->value('id');

                        if (! $employeeId) {
                            throw new \RuntimeException(
                                "Payroll row skipped — employee not found: {$empCode}. "
                                . 'Upload employees.csv first.'
                            );
                        }

                        // Rows arrive with canonical keys from CsvColumnMapper
                        $gross = CsvNormalizer::normalizeFloat($row['gross_salary'] ?? null);
                        $net   = CsvNormalizer::normalizeFloat($row['net_salary']   ?? null);
                        $pf    = CsvNormalizer::normalizeFloat($row['pf_employee']  ?? null);
                        $esi   = CsvNormalizer::normalizeFloat($row['esi_employee'] ?? null);
                        $pt    = CsvNormalizer::normalizeFloat($row['professional_tax'] ?? null);

                        if ($gross <= 0) {
                            throw new \InvalidArgumentException(
                                "Invalid gross_salary (must be > 0) for employee: {$empCode}"
                            );
                        }
                        if ($net > $gross) {
                            throw new \InvalidArgumentException(
                                "net_salary ({$net}) exceeds gross_salary ({$gross}) for employee: {$empCode}"
                            );
                        }

                        DB::table('workforce_payroll_entry')->updateOrInsert(
                            [
                                'tenant_id'        => $batch->tenant_id,
                                'branch_id'        => $branchId,
                                'payroll_cycle_id' => $cycleId,
                                'employee_id'      => $employeeId,
                            ],
                            [
                            'total_days_worked' => CsvNormalizer::normalizeInt($row['total_days_worked'] ?? null, 26),
                            'paid_leave_days'   => CsvNormalizer::normalizeInt($row['paid_leave_days']   ?? null),
                            'unpaid_leave_days' => CsvNormalizer::normalizeInt($row['unpaid_leave_days'] ?? null),
                            'overtime_hours'    => CsvNormalizer::normalizeFloat($row['overtime_hours']  ?? null),
                            'basic_earned'      => CsvNormalizer::normalizeFloat($row['basic_earned']    ?? null),
                            'da_earned'         => CsvNormalizer::normalizeFloat($row['da_earned']       ?? null),
                            'hra_earned'        => CsvNormalizer::normalizeFloat($row['hra_earned']      ?? null),
                            'other_allowances'  => CsvNormalizer::normalizeFloat($row['other_allowances'] ?? null),
                            'overtime_wages'    => CsvNormalizer::normalizeFloat($row['overtime_wages']  ?? null),
                            'gross_salary'      => $gross,
                            'pf_employee'       => $pf,
                            'esi_employee'      => $esi,
                            'professional_tax'  => $pt,
                            'fines'             => CsvNormalizer::normalizeFloat($row['fines']            ?? null),
                            'advances'          => CsvNormalizer::normalizeFloat($row['advances']         ?? null),
                            'other_deductions'  => CsvNormalizer::normalizeFloat($row['other_deductions'] ?? null),
                            'total_deductions'  => $gross - $net,
                            'net_salary'        => $net,
                            'payment_date'      => $row['payment_date'] ?? null,
                            'payment_mode'      => $row['payment_mode'] ?? 'Bank Transfer',
                            'created_at'        => now(),
                            'updated_at'        => now(),
                            'deleted_at'         => null,
                            ]
                        );
                        $inserted++;
                    }

                } elseif ($datasetType === 'attendance') {
                    // Derive the period start date safely
                    $periodStart = null;
                    if (! empty($batch->period_from)) {
                        $periodStart = \Carbon\Carbon::parse($batch->period_from);
                    } elseif (! empty($batch->period_month) && ! empty($batch->period_year)) {
                        $periodStart = \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1);
                    } else {
                        $periodStart = now()->startOfMonth();
                    }

                    foreach ($rows as $row) {
                        $empCode = trim($row['employee_code'] ?? '');
                        if ($empCode === '') continue;

                        $employeeId = DB::table('workforce_employee')
                            ->where('tenant_id', $batch->tenant_id)
                            ->where('employee_code', $empCode)
                            ->whereNull('deleted_at')
                            ->value('id');

                        if (! $employeeId) {
                            throw new \RuntimeException(
                                "Attendance: employee not found — {$empCode}. Upload employees.csv first."
                            );
                        }

                        $workingDays = trim($row['working_days'] ?? '');
                        if (! is_numeric($workingDays)) {
                            throw new \InvalidArgumentException(
                                "Attendance: invalid working_days value '{$workingDays}' for employee {$empCode}."
                            );
                        }

                        $workingDays = (int) $workingDays;
                        $absentDays  = CsvNormalizer::normalizeInt($row['absent_days'] ?? $row['absent'] ?? null);
                        $presentDays = max(0, $workingDays - $absentDays);
                        $otHours     = CsvNormalizer::normalizeFloat($row['overtime_hours'] ?? null);

                        // If CSV has an explicit date, store single row
                        $explicitDate = trim($row['attendance_date'] ?? $row['date'] ?? '');
                        if ($explicitDate !== '' && strtotime($explicitDate)) {
                            DB::table('workforce_attendance')->updateOrInsert(
                                [
                                    'tenant_id'       => $batch->tenant_id,
                                    'employee_id'     => $employeeId,
                                    'attendance_date' => $explicitDate,
                                ],
                                [
                                    'branch_id'      => $branchId,
                                    'status'         => ($row['attendance_status'] ?? $row['status'] ?? 'present'),
                                    'overtime_hours' => $otHours,
                                    'remarks'        => "CSV import: {$presentDays}/{$workingDays} days present",
                                    'deleted_at'     => null,
                                    'updated_at'     => now(),
                                    'created_at'     => now(),
                                ]
                            );
                            $inserted++;
                            continue;
                        }

                        // Summary mode: generate one row per working day of the month
                        // so that date-range queries (FormXVI, Form25, etc.) work correctly.
                        $daysInMonth  = $periodStart->daysInMonth;
                        $absentFilled = 0;

                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $date = $periodStart->copy()->day($day)->toDateString();
                            $dayOfWeek = $periodStart->copy()->day($day)->dayOfWeek;

                            // Skip Sundays as weekly off
                            if ($dayOfWeek === 0) continue;

                            // Mark absent days from the end of the month
                            $remainingDays = $daysInMonth - $day + 1;
                            $isAbsent = ($absentFilled < $absentDays) && ($remainingDays <= ($absentDays - $absentFilled));
                            if ($isAbsent) $absentFilled++;

                            DB::table('workforce_attendance')->updateOrInsert(
                                [
                                    'tenant_id'       => $batch->tenant_id,
                                    'employee_id'     => $employeeId,
                                    'attendance_date' => $date,
                                ],
                                [
                                    'branch_id'      => $branchId,
                                    'status'         => $isAbsent ? 'absent' : 'present',
                                    'overtime_hours' => ($day === 1) ? $otHours : 0,
                                    'deleted_at'     => null,
                                    'updated_at'     => now(),
                                    'created_at'     => now(),
                                ]
                            );
                        }
                        $inserted++;
                    }
                }

                return $inserted;
            });

            Log::info('CSV upload completed', [
                'batch_id'         => $batchId,
                'dataset_type'     => $datasetType,
                'records_inserted' => $recordsInserted,
            ]);

            // Auto-trigger form generation — wrapped so a generation failure
            // never rolls back the already-committed CSV data
            try {
                $generationResult = $this->maybeGenerateForms($batch);
            } catch (\Throwable $genEx) {
                Log::error('maybeGenerateForms threw unexpectedly', [
                    'batch_id' => $batchId,
                    'error'    => $genEx->getMessage(),
                ]);
                $generationResult = ['triggered' => false, 'reason' => $genEx->getMessage()];
            }

            // Discard any stray output (BOM, whitespace) before JSON response
            if (ob_get_level()) ob_clean();

            return response()->json([
                'status'           => 'success',
                'message'          => "Successfully imported {$recordsInserted} {$datasetType} records",
                'records_inserted' => $recordsInserted,
                'dataset_type'     => $datasetType,
                'generation'       => $generationResult,
            ]);

        } catch (\Throwable $e) {
            Log::error('CSV upload failed', [
                'batch_id'     => $batchId,
                'dataset_type' => $datasetType,
                'error'        => $e->getMessage(),
                'file'         => $e->getFile() . ':' . $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
