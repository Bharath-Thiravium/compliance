<?php

namespace App\Http\Controllers;

use App\Services\Compliance\CsvNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplianceDataUploadController extends Controller
{
    public function showForm()
    {
        return view('compliance.csv_upload');
    }

    public function downloadTemplate(string $type = 'employees')
    {
        $templates = [
            'employees' => [
                'filename' => 'sample_employees.csv',
                'content'  => implode("\n", [
                    'employee_code,name,father_name,date_of_birth,gender,mobile,designation,department,date_of_joining,uan,esic_ip,pf_number,pan,bank_account,bank_name,ifsc,basic_salary,permanent_address,status',
                    'EMP001,Arumugam S,Subramaniam A,1985-06-15,Male,9876543210,Supervisor,Production,2020-01-10,100123456789,1234567890,PF001234,ABCDE1234F,12345678901234,State Bank of India,SBIN0001234,18000,12 Main Road Chennai TN 600001,active',
                    'EMP002,Balamurugan K,Krishnan B,1990-03-22,Male,9876543211,Technician,Maintenance,2021-04-01,100123456790,1234567891,PF001235,FGHIJ5678K,12345678901235,Indian Bank,IDIB000M123,15000,45 Anna Nagar Chennai TN 600040,active',
                    'EMP003,Kavitha R,Rajan P,1992-11-08,Female,9876543212,Clerk,Administration,2022-07-15,100123456791,1234567892,PF001236,KLMNO9012L,12345678901236,Canara Bank,CNRB0001234,12000,78 T Nagar Chennai TN 600017,active',
                ]),
            ],
            'payroll' => [
                'filename' => 'sample_payroll.csv',
                'content'  => implode("\n", [
                    'employee_code,gross_salary,basic_salary,hra,conveyance,other_allowance,overtime,bonus,deductions,pf,esi,professional_tax,net_salary,salary_month,salary_year',
                    'EMP001,18000,9000,3600,1600,3800,0,0,1956,1080,135,200,16044,1,2025',
                    'EMP002,15000,7500,3000,1600,2900,500,0,1631,900,113,200,13869,1,2025',
                    'EMP003,12000,6000,2400,1600,2000,0,0,1304,720,90,200,10696,1,2025',
                ]),
            ],
            'attendance' => [
                'filename' => 'sample_attendance.csv',
                'content'  => implode("\n", [
                    'employee_code,working_days,present_days,absent_days,weekly_off,paid_leave,ot_hours,attendance_month,attendance_year',
                    'EMP001,26,26,0,4,0,0,1,2025',
                    'EMP002,26,25,1,4,0,4,1,2025',
                    'EMP003,26,24,2,4,1,0,1,2025',
                ]),
            ],
        ];

        if (! isset($templates[$type])) {
            abort(404);
        }

        $tpl = $templates[$type];

        return response($tpl['content'], 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $tpl['filename'] . '"',
        ]);
    }

    public function upload(Request $request)
    {
        // Validate file presence and period — always returns JSON on failure
        // because the JS fetch sends Accept: application/json.
        $request->validate([
            'employees_file'  => 'required|file|max:5120',
            'payroll_file'    => 'required|file|max:5120',
            'attendance_file' => 'required|file|max:5120',
            'period_from'     => 'required|date',
            'period_to'       => 'required|date|after_or_equal:period_from',
        ]);

        $user     = Auth::user();
        $tenantId = $user->tenant_id;
        $branchId = $this->resolveUploadBranchId($tenantId, $user->branch_id, $user->id);

        // Wrap everything — including CSV parsing — in one try/catch so that
        // any InvalidArgumentException from parseCsv returns JSON, not a 500.
        try {
            $employees   = $this->parseCsv($request->file('employees_file'),  'employees');
            $payrollRows = $this->parseCsv($request->file('payroll_file'),    'payroll');
            $attendRows  = $this->parseCsv($request->file('attendance_file'), 'attendance');

            Log::info('CSV parsed', [
                'tenant_id'  => $tenantId,
                'branch_id'  => $branchId,
                'employees'  => count($employees),
                'payroll'    => count($payrollRows),
                'attendance' => count($attendRows),
            ]);

            $this->validateConsistency($employees, $payrollRows, $attendRows);

            $counts = DB::transaction(function () use (
                $tenantId, $branchId, $employees, $payrollRows, $attendRows, $request
            ) {
                $empCodeToId  = $this->insertEmployees($employees, $tenantId, $branchId);
                $cycleId      = $this->resolvePayrollCycle(
                    $tenantId,
                    $request->input('period_from'),
                    $request->input('period_to')
                );
                $payrollCount = $this->insertPayroll($payrollRows, $empCodeToId, $tenantId, $branchId, $cycleId);
                $attendCount  = $this->insertAttendance(
                    $attendRows, $empCodeToId, $tenantId, $branchId,
                    $request->input('period_from')
                );

                return [
                    'employees'  => count($empCodeToId),
                    'payroll'    => $payrollCount,
                    'attendance' => $attendCount,
                ];
            });

            Log::info('Multi-CSV upload success', ['tenant_id' => $tenantId, 'counts' => $counts]);

            return response()->json([
                'status'  => 'success',
                'message' => 'All datasets uploaded successfully',
                'counts'  => $counts,
            ]);

        } catch (\Throwable $e) {
            Log::error('Multi-CSV upload failed', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function resolveUploadBranchId(int $tenantId, ?int $branchId, int $userId): int
    {
        if ($branchId) {
            $exists = DB::table('branches')
                ->where('tenant_id', $tenantId)
                ->where('id', $branchId)
                ->exists();

            if ($exists) {
                return $branchId;
            }
        }

        $resolved = DB::table('branches')
            ->where('tenant_id', $tenantId)
            ->orderBy('id')
            ->value('id');

        if (! $resolved) {
            $resolved = DB::table('branches')->insertGetId([
                'tenant_id'   => $tenantId,
                'branch_name' => 'Main Branch',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        DB::table('users')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->whereNull('branch_id')
            ->update([
                'branch_id'  => $resolved,
                'updated_at' => now(),
            ]);

        return (int) $resolved;
    }

    // ── Employee Payload Builder ──────────────────────────────────────────────

    private function buildEmployeePayload(array $row, int $tenantId, int $branchId, bool $withTimestamps = true): array
    {
        $payload = [
            'tenant_id'         => $tenantId,
            'branch_id'         => $branchId,
            'employee_code'     => $row['employee_code'],
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
            'date_of_joining'   => $this->parseDate($row['date_of_joining'] ?? null) ?? now()->toDateString(),
            'pf_number'         => CsvNormalizer::normalizePF($row['pf_number'] ?? null),
            'esi_number'        => CsvNormalizer::normalizeESI($row['esi_number'] ?? null),
            'uan_number'        => CsvNormalizer::normalizeUAN($row['uan_number'] ?? $row['pf_number'] ?? null),
            'pan'               => $row['pan']               ?? null,
            'aadhaar'           => $row['aadhaar']           ?? null,
            'bank_account'      => $row['bank_account']      ?? null,
            'bank_name'         => $row['bank_name']         ?? null,
            'ifsc'              => $row['ifsc']              ?? null,
            'basic_salary'      => CsvNormalizer::normalizeFloat($row['basic_salary'] ?? null),
            'status'            => 'active',
            'deleted_at'         => null,
            'updated_at'        => now(),
        ];

        if ($withTimestamps) {
            $payload['created_at'] = now();
        }

        return $payload;
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    // ── CSV Parsing ───────────────────────────────────────────────────────────

    /**
     * Parse a CSV file using CsvColumnMapper for intelligent alias resolution.
     * Returns rows keyed by CANONICAL field names (e.g. 'gross_salary', 'uan_number').
     */
    private function parseCsv(\Illuminate\Http\UploadedFile $file, string $type): array
    {
        $path   = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \InvalidArgumentException("CSV ({$type}): cannot open uploaded file.");
        }

        // ── Detect delimiter from first line (comma vs semicolon) ─────────────
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \InvalidArgumentException("CSV ({$type}): file is empty.");
        }

        // Strip UTF-8 BOM that Excel adds — causes invisible garbage in first header
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        rewind($handle);

        // ── Header row ───────────────────────────────────────────────────────
        $rawHeaders = fgetcsv($handle, 4096, $delimiter);
        if (! $rawHeaders) {
            fclose($handle);
            throw new \InvalidArgumentException("CSV ({$type}): could not read header row.");
        }

        // Strip BOM from first cell only
        $rawHeaders[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeaders[0]);

        // ── Alias-aware mapping: raw header → canonical field name ─────────────
        $skipped       = [];
        $headerMapping = \App\Services\Compliance\CsvColumnMapper::mapHeaders($rawHeaders, $type, $skipped);
        $required      = \App\Services\Compliance\CsvColumnMapper::requiredFields($type);
        $missing       = array_diff($required, array_keys($headerMapping));

        if (! empty($missing)) {
            fclose($handle);
            throw new \InvalidArgumentException(
                "CSV ({$type}): missing required columns: " . implode(', ', $missing) .
                ". Found: " . implode(', ', $rawHeaders)
            );
        }

        if (! empty($skipped)) {
            Log::debug("CSV ({$type}): unrecognised columns skipped", ['skipped' => $skipped]);
        }

        // ── Data rows — returned with CANONICAL keys ───────────────────────────
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
                continue; // skip blank-code rows (e.g. footer totals)
            }

            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \InvalidArgumentException("CSV ({$type}): no valid data rows found.");
        }

        Log::debug("CSV ({$type}) parsed", [
            'rows'      => count($rows),
            'delimiter' => $delimiter,
            'mapped'    => array_keys($headerMapping),
        ]);

        return $rows;
    }

    // ── Cross-file Consistency ────────────────────────────────────────────────

    private function validateConsistency(array $employees, array $payrollRows, array $attendRows): void
    {
        $empCodes     = array_column($employees,   'employee_code');
        $payrollCodes = array_column($payrollRows, 'employee_code');
        $attendCodes  = array_column($attendRows,  'employee_code');

        // Duplicate employee codes
        $dupes = array_keys(array_filter(array_count_values($empCodes), fn($c) => $c > 1));
        if (! empty($dupes)) {
            throw new \InvalidArgumentException(
                'Duplicate employee codes in employees.csv: ' . implode(', ', $dupes)
            );
        }

        // Payroll codes not in employees
        $orphanPayroll = array_diff($payrollCodes, $empCodes);
        if (! empty($orphanPayroll)) {
            throw new \InvalidArgumentException(
                'Payroll data for unknown employee codes: ' . implode(', ', $orphanPayroll)
            );
        }

        // Attendance codes not in employees
        $orphanAttend = array_diff($attendCodes, $empCodes);
        if (! empty($orphanAttend)) {
            throw new \InvalidArgumentException(
                'Attendance data for unknown employee codes: ' . implode(', ', $orphanAttend)
            );
        }
    }

    // ── Insert Employees ──────────────────────────────────────────────────────

    /**
     * Upsert employees and return [employee_code => id] map.
     */
    private function insertEmployees(array $rows, int $tenantId, int $branchId): array
    {
        $map = [];
        $uploadedCodes = collect($rows)
            ->pluck('employee_code')
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(! empty($uploadedCodes), fn ($query) => $query->whereNotIn('employee_code', $uploadedCodes))
            ->update([
                'status' => 'inactive',
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        foreach ($rows as $row) {
            $code = $row['employee_code'];

            // Check if already exists for this tenant
            $existing = DB::table('workforce_employee')
                ->where('tenant_id', $tenantId)
                ->where('employee_code', $code)
                ->value('id');

            if ($existing) {
                DB::table('workforce_employee')
                    ->where('id', $existing)
                    ->update(array_diff_key(
                        $this->buildEmployeePayload($row, $tenantId, $branchId, false),
                        ['tenant_id' => 1, 'employee_code' => 1, 'created_at' => 1]
                    ));
                $map[$code] = $existing;
                continue;
            }

            $id = DB::table('workforce_employee')->insertGetId(
                $this->buildEmployeePayload($row, $tenantId, $branchId)
            );

            $map[$code] = $id;
        }

        return $map;
    }

    // ── Payroll Cycle ─────────────────────────────────────────────────────────

    private function resolvePayrollCycle(int $tenantId, string $from, string $to): int
    {
        $existing = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereDate('period_from', $from)
            ->whereDate('period_to', $to)
            ->value('id');

        if ($existing) {
            return $existing;
        }

        return DB::table('workforce_payroll_cycle')->insertGetId([
            'tenant_id'    => $tenantId,
            'cycle_name'   => 'CSV Import ' . \Carbon\Carbon::parse($from)->format('M Y'),
            'period_from'  => $from,
            'period_to'    => $to,
            'status'       => 'processed',
            'processed_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    // ── Insert Payroll ────────────────────────────────────────────────────────

    private function insertPayroll(array $rows, array $empMap, int $tenantId, int $branchId, int $cycleId): int
    {
        $count = 0;

        DB::table('workforce_payroll_entry')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('payroll_cycle_id', $cycleId)
            ->delete();

        foreach ($rows as $row) {
            $code = $row['employee_code'];

            if (! isset($empMap[$code])) {
                throw new \RuntimeException("Payroll data mismatch for employee {$code}");
            }

            // All keys are canonical names thanks to CsvColumnMapper in parseCsv()
            $gross       = (float) ($row['gross_salary']     ?? 0);
            $net         = (float) ($row['net_salary']       ?? 0);
            $basic       = (float) ($row['basic_earned']     ?? $row['basic_salary'] ?? 0);
            $pf          = (float) ($row['pf_employee']      ?? 0);
            $esi         = (float) ($row['esi_employee']     ?? 0);
            $pt          = (float) ($row['professional_tax'] ?? 0);
            $otHours     = (float) ($row['overtime_hours']   ?? 0);
            $otWages     = (float) ($row['overtime_wages']   ?? 0);
            $workingDays = (int)   ($row['total_days_worked'] ?? 26);
            $absent      = (int)   ($row['unpaid_leave_days'] ?? 0);
            $totalDeduct = $gross - $net;

            // Numeric sanity
            if ($gross <= 0) {
                throw new \InvalidArgumentException("Invalid gross_salary for employee {$code}");
            }
            if ($net > $gross) {
                throw new \InvalidArgumentException("net_salary exceeds gross_salary for employee {$code}");
            }

            DB::table('workforce_payroll_entry')->updateOrInsert(
                [
                    'tenant_id'        => $tenantId,
                    'branch_id'        => $branchId,
                    'payroll_cycle_id' => $cycleId,
                    'employee_id'      => $empMap[$code],
                ],
                [
                'total_days_worked' => $workingDays,
                'paid_leave_days'   => (int) ($row['paid_leave_days'] ?? 0),
                'unpaid_leave_days' => $absent,
                'overtime_hours'    => $otHours,
                'basic_earned'      => $basic,
                'da_earned'         => (float) ($row['da_earned']           ?? 0),
                'hra_earned'        => (float) ($row['hra_earned']          ?? 0),
                'other_allowances'  => (float) ($row['other_allowances']    ?? 0),
                'overtime_wages'    => $otWages,
                'gross_salary'      => $gross,
                'pf_employee'       => $pf,
                'esi_employee'      => $esi,
                'professional_tax'  => $pt,
                'fines'             => (float) ($row['fines']              ?? 0),
                'advances'          => (float) ($row['advances']           ?? 0),
                'other_deductions'  => (float) ($row['other_deductions']   ?? 0),
                'total_deductions'  => $totalDeduct,
                'net_salary'        => $net,
                'payment_date'      => $row['payment_date'] ?? null,
                'payment_mode'      => $row['payment_mode'] ?? 'Bank Transfer',
                'created_at'        => now(),
                'updated_at'        => now(),
                'deleted_at'         => null,
                ]
            );

            $count++;
        }

        return $count;
    }

    // ── Insert Attendance ─────────────────────────────────────────────────────

    private function insertAttendance(
        array $rows, array $empMap, int $tenantId, int $branchId, ?string $periodFrom = null
    ): int {
        // Use the upload's period_from so attendance is stored in the correct month,
        // not always the current calendar month.
        $periodStart = $periodFrom
            ? \Carbon\Carbon::parse($periodFrom)->startOfMonth()
            : now()->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $count = 0;

        DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('attendance_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->delete();

        foreach ($rows as $row) {
            $code = $row['employee_code'];

            if (! isset($empMap[$code])) {
                throw new \RuntimeException("Attendance data mismatch for employee {$code}");
            }

            $workingDays = (int) ($row['working_days'] ?? 26);
            $absentDays  = (int) ($row['absent_days']  ?? 0);
            $otHours     = (float) ($row['overtime_hours'] ?? 0);

            // Single-row mode: CSV has an explicit date
            $explicitDate = trim($row['attendance_date'] ?? '');
            if ($explicitDate !== '' && strtotime($explicitDate)) {
                DB::table('workforce_attendance')->updateOrInsert(
                    [
                        'tenant_id'       => $tenantId,
                        'employee_id'     => $empMap[$code],
                        'attendance_date' => $explicitDate,
                    ],
                    [
                        'branch_id'      => $branchId,
                        'status'         => ($row['attendance_status'] ?? 'present'),
                        'overtime_hours' => $otHours,
                        'deleted_at'     => null,
                        'updated_at'     => now(),
                        'created_at'     => now(),
                    ]
                );
                $count++;
                continue;
            }

            // Summary mode: expand into per-day rows for the correct pay-period month
            $daysInMonth  = $periodStart->daysInMonth;
            $absentFilled = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date      = $periodStart->copy()->day($day)->toDateString();
                $dayOfWeek = $periodStart->copy()->day($day)->dayOfWeek;
                if ($dayOfWeek === 0) continue; // skip Sundays

                $remainingDays = $daysInMonth - $day + 1;
                $isAbsent = ($absentFilled < $absentDays) && ($remainingDays <= ($absentDays - $absentFilled));
                if ($isAbsent) $absentFilled++;

                DB::table('workforce_attendance')->updateOrInsert(
                    [
                        'tenant_id'       => $tenantId,
                        'employee_id'     => $empMap[$code],
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
            $count++;
        }

        return $count;
    }
}
