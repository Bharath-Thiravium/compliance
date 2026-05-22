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

            // Store in session only — no DB writes
            session(["manual_batch_data_{$batchId}" => [
                'employees'  => $validated['employees_data']  ?? '',
                'attendance' => $validated['attendance_data'] ?? '',
                'payroll'    => $validated['payroll_data']    ?? '',
            ]]);

            Log::info("Manual data stored in session for batch {$batchId}");

            return response()->json(['status' => 'success', 'message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            Log::error('Manual data save error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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

        Log::info('CSV upload started', [
            'batch_id'     => $batchId,
            'dataset_type' => $datasetType,
            'filename'     => $file->getClientOriginalName(),
            'row_count'    => count($rows),
            'tenant_id'    => $batch->tenant_id,
        ]);

        // ── 7. Transaction — all inserts or nothing ───────────────────────────
        try {
            $recordsInserted = DB::transaction(function () use (
                $batch, $datasetType, $rows
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
                                'branch_id'       => $batch->branch_id,
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

                        DB::table('workforce_payroll_entry')->insertOrIgnore([
                            'tenant_id'         => $batch->tenant_id,
                            'branch_id'         => $batch->branch_id,
                            'payroll_cycle_id'  => $cycleId,
                            'employee_id'       => $employeeId,
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
                        ]);
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
                                    'branch_id'      => $batch->branch_id,
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
                                    'branch_id'      => $batch->branch_id,
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
