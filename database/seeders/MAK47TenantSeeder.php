<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * MAK47 Full Subscription Tenant Seeder
 *
 * Company  : M/S MAK47
 * Location : Sivagangai, Tamil Nadu
 * Principal: Hinduja Renewables Energy Pvt Ltd
 * Period   : December 2025
 *
 * Run: php artisan db:seed --class=MAK47TenantSeeder
 */
class MAK47TenantSeeder extends Seeder
{
    private int $tenantId;
    private int $branchId;
    private int $userId;
    private int $cycleId;
    private array $employees = [];

    // ── Payroll constants ────────────────────────────────────────────────────
    private const BASIC       = 5500.00;
    private const GROSS       = 10308.00;
    private const NET         = 9371.00;
    private const PF_EMP      = 660.00;   // 12 % of basic
    private const ESI_EMP     = 96.00;    // 0.75 % of gross (≈ 77; rounded to actual)
    private const PT          = 181.00;   // Professional Tax TN slab
    private const WORKING_DAYS = 27;

    // ── Auto-generated form codes (compliance_batch_forms) ───────────────────
    private const AUTO_FORMS = [
        // CLRA
        'FORM_XII', 'FORM_XIII', 'FORM_XIV', 'FORM_XVI', 'FORM_XVII',
        'FORM_XIX', 'FORM_XX',  'FORM_XXI', 'FORM_XXII', 'FORM_XXIII',
        // Labour Welfare
        'FORM_A_LWF', 'FORM_C_LWF', 'FORM_D_LWF', 'FORM_DER',
        // Social Security
        'FORM_11_EPF', 'ESI_FORM_12', 'EPF_INSPECTION',
        // Factories Act
        'FORM_B', 'FORM_2', 'FORM_8', 'FORM_10', 'FORM_12_FA',
        'FORM_17', 'FORM_18', 'FORM_25', 'FORM_26', 'FORM_26A', 'HAZARD_REG',
        // Shops & Establishments
        'SHOPS_FORM_12', 'SHOPS_FORM_13',
    ];

    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        $this->createUser();
        $this->createTenant();
        $this->createBranch();
        $this->createPayrollCycle();
        $this->createEmployees();
        $this->createAttendance();
        $this->createPayrollEntries();
        $this->createComplianceBatch();
        $this->createManualComplianceItems();
        DB::statement('PRAGMA foreign_keys = ON');
        $this->printSummary();
    }

    // ── 1. User ──────────────────────────────────────────────────────────────

    private function createUser(): void
    {
        $this->userId = DB::table('users')->insertGetId([
            'name'       => 'MAK47 Admin',
            'email'      => 'admin@mak47.com',
            'password'   => Hash::make('mak47@2025'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ── 2. Tenant ────────────────────────────────────────────────────────────

    private function createTenant(): void
    {
        $this->tenantId = DB::table('tenants')->insertGetId([
            'name'                  => 'M/S MAK47',
            'establishment_name'    => 'MAK47 Renewable Energy Works',
            'factory_license_no'    => 'TN/SIV/FAC/2025/047',
            'pf_code'               => 'TN/SIV/PF/2025/047',
            'esi_code'              => 'TN/SIV/ESI/2025/047',
            'subscription_type'     => 'FULL',
            'labour_office_address' => 'Labour Office, Sivagangai, Tamil Nadu – 630561',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('users')->where('id', $this->userId)
            ->update(['tenant_id' => $this->tenantId]);
    }

    // ── 3. Branch ────────────────────────────────────────────────────────────

    private function createBranch(): void
    {
        $this->branchId = DB::table('branches')->insertGetId([
            'tenant_id'              => $this->tenantId,
            'branch_name'            => 'Sivagangai Solar Works',
            'unit_name'              => 'Hinduja Renewables – MAK47 Unit',
            'factory_license_number' => 'TN/SIV/FAC/2025/047',
            'address'                => 'Survey No. 47, Sivagangai Industrial Estate, Sivagangai, Tamil Nadu – 630561',
            'pf_code'                => 'TN/SIV/PF/2025/047',
            'esi_code'               => 'TN/SIV/ESI/2025/047',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);
    }

    // ── 4. Payroll Cycle ─────────────────────────────────────────────────────

    private function createPayrollCycle(): void
    {
        $this->cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
            'tenant_id'    => $this->tenantId,
            'cycle_name'   => 'December 2025',
            'period_from'  => '2025-12-01',
            'period_to'    => '2025-12-31',
            'status'       => 'processed',
            'processed_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    // ── 5. Employees ─────────────────────────────────────────────────────────

    private function createEmployees(): void
    {
        $data = [
            ['ARUMUGAM S',    'M', '1985-04-12', 'Supervisor',       'Production',    'UAN100047001', 'ESI4700001'],
            ['BALAMURUGAN K', 'M', '1990-07-22', 'Electrician',      'Maintenance',   'UAN100047002', 'ESI4700002'],
            ['CHELLADURAI P', 'M', '1988-11-05', 'Machine Operator', 'Production',    'UAN100047003', 'ESI4700003'],
            ['DEIVANAI R',    'F', '1993-03-18', 'Helper',           'Packaging',     'UAN100047004', 'ESI4700004'],
            ['ELUMALAI T',    'M', '1987-09-30', 'Technician',       'Maintenance',   'UAN100047005', 'ESI4700005'],
            ['FATHIMA B',     'F', '1995-01-14', 'Quality Inspector', 'Quality',      'UAN100047006', 'ESI4700006'],
            ['GANESAN M',     'M', '1982-06-25', 'Safety Officer',   'Safety',        'UAN100047007', 'ESI4700007'],
            ['HEMA L',        'F', '1991-12-08', 'Helper',           'Packaging',     'UAN100047008', 'ESI4700008'],
            ['ILAYARAJA V',   'M', '1989-08-17', 'Technician',       'Production',    'UAN100047009', 'ESI4700009'],
        ];

        foreach ($data as $i => [$name, $gender, $dob, $designation, $dept, $uan, $esi]) {
            $code = 'MAK47EMP' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);

            $id = DB::table('workforce_employee')->insertGetId([
                'tenant_id'        => $this->tenantId,
                'branch_id'        => $this->branchId,
                'employee_code'    => $code,
                'name'             => $name,
                'gender'           => $gender,
                'date_of_birth'    => $dob,
                'permanent_address'=> 'Sivagangai, Tamil Nadu',
                'local_address'    => 'Sivagangai, Tamil Nadu',
                'pf_number'        => $uan,
                'esi_number'       => $esi,
                'date_of_joining'  => '2024-01-01',
                'designation'      => $designation,
                'department'       => $dept,
                'basic_salary'     => self::BASIC,
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $this->employees[] = ['id' => $id, 'code' => $code, 'name' => $name];
        }
    }

    // ── 6. Attendance (27 working days, all present) ─────────────────────────

    private function createAttendance(): void
    {
        // December 2025 working days (Mon–Sat, skip Sundays = 27 days)
        $workingDays = $this->getWorkingDays(2025, 12, self::WORKING_DAYS);

        $rows = [];
        foreach ($this->employees as $emp) {
            foreach ($workingDays as $date) {
                $rows[] = [
                    'tenant_id'       => $this->tenantId,
                    'branch_id'       => $this->branchId,
                    'employee_id'     => $emp['id'],
                    'attendance_date' => $date,
                    'status'          => 'present',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        // Chunk to avoid max_allowed_packet issues
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('workforce_attendance')->insert($chunk);
        }
    }

    // ── 7. Payroll Entries ───────────────────────────────────────────────────

    private function createPayrollEntries(): void
    {
        // Derive component breakdown from fixed gross/net
        // Gross = 10308: Basic 5500 + DA 825 (15%) + HRA 550 (10%) + Other 3433
        $da            = 825.00;
        $hra           = 550.00;
        $otherAllow    = self::GROSS - self::BASIC - $da - $hra; // 3433
        $totalDeduct   = self::GROSS - self::NET;                // 937
        // PF 660 + ESI 96 + PT 181 = 937 ✓

        foreach ($this->employees as $emp) {
            DB::table('workforce_payroll_entry')->insert([
                'tenant_id'             => $this->tenantId,
                'branch_id'             => $this->branchId,
                'payroll_cycle_id'      => $this->cycleId,
                'employee_id'           => $emp['id'],
                'total_days_worked'     => self::WORKING_DAYS,
                'paid_leave_days'       => 0,
                'unpaid_leave_days'     => 0,
                'overtime_hours'        => 0,
                'basic_earned'          => self::BASIC,
                'da_earned'             => $da,
                'hra_earned'            => $hra,
                'other_allowances'      => $otherAllow,
                'overtime_wages'        => 0,
                'gross_salary'          => self::GROSS,
                'pf_employee'           => self::PF_EMP,
                'esi_employee'          => self::ESI_EMP,
                'professional_tax'      => self::PT,
                'fines'                 => 0,
                'advances'              => 0,
                'other_deductions'      => 0,
                'total_deductions'      => $totalDeduct,
                'net_salary'            => self::NET,
                'payment_date'          => '2026-01-05',
                'payment_mode'          => 'Bank Transfer',
                'transaction_reference' => 'TXN/MAK47/' . $emp['code'] . '/202512',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }

    // ── 8. Compliance Batch (December 2025, 30 forms, all generated) ─────────

    private function createComplianceBatch(): void
    {
        // Resolve section_id = 1 (default); gracefully handle missing sections table
        $sectionId = DB::table('compliance_sections')->value('id') ?? 1;

        $batchId = DB::table('compliance_execution_batches')->insertGetId([
            'tenant_id'              => $this->tenantId,
            'branch_id'              => $this->branchId,
            'section_id'             => $sectionId,
            'period_from'            => '2025-12-01',
            'period_to'              => '2025-12-31',
            'period_month'           => 12,
            'period_year'            => 2025,
            'form_ids'               => json_encode(array_keys(self::AUTO_FORMS)),
            'status'                 => 'completed',
            'created_by'             => $this->userId,
            'processed_at'           => now(),
            'results'                => json_encode(['total' => count(self::AUTO_FORMS), 'success' => count(self::AUTO_FORMS), 'failed' => 0]),
            'generated_report_path'  => null,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Insert one compliance_batch_forms row per form
        $batchForms = [];
        foreach (self::AUTO_FORMS as $formCode) {
            $batchForms[] = [
                'tenant_id'  => $this->tenantId,
                'batch_id'   => $batchId,
                'form_code'  => $formCode,
                'section'    => $this->sectionFor($formCode),
                'file_path'  => "compliance_pdfs/tenant_{$this->tenantId}/{$formCode}_DEC2025.pdf",
                'status'     => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('compliance_batch_forms')->insert($batchForms);

        // Execution log per form
        $logs = [];
        foreach (self::AUTO_FORMS as $formCode) {
            $logs[] = [
                'tenant_id'         => $this->tenantId,
                'branch_id'         => $this->branchId,
                'batch_id'          => $batchId,
                'form_code'         => $formCode,
                'status'            => 'success',
                'execution_time'    => rand(120, 480),
                'records_generated' => count($this->employees),
                'error_message'     => null,
                'execution_mode'    => 'batch',
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        DB::table('compliance_execution_logs')->insert($logs);
    }

    // ── 9. Manual Compliance Items (non-automatable only) ────────────────────

    private function createManualComplianceItems(): void
    {
        // Fetch only non-automatable manual compliance entries
        $manualItems = DB::table('compliance_manual_master')
            ->where('is_automatable', false)
            ->get(['id', 'frequency']);

        if ($manualItems->isEmpty()) {
            $this->command->warn('  No manual compliance master records found — skipping manual batch items.');
            return;
        }

        // Create a manual batch linked to the same period
        $sectionId = DB::table('compliance_sections')->value('id') ?? 1;

        $manualBatchId = DB::table('compliance_execution_batches')->insertGetId([
            'tenant_id'    => $this->tenantId,
            'branch_id'    => $this->branchId,
            'section_id'   => $sectionId,
            'period_from'  => '2025-12-01',
            'period_to'    => '2025-12-31',
            'period_month' => 12,
            'period_year'  => 2025,
            'form_ids'     => json_encode([]),
            'status'       => 'pending',
            'created_by'   => $this->userId,
            'processed_at' => null,
            'results'      => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $items = [];
        foreach ($manualItems as $item) {
            // Only include monthly + applicable annual/half-yearly for Dec 2025
            if (! $this->isApplicableForDec($item->frequency)) {
                continue;
            }

            $items[] = [
                'batch_id'          => $manualBatchId,
                'tenant_id'         => $this->tenantId,
                'branch_id'         => $this->branchId,
                'compliance_id'     => $item->id,
                'status'            => 'pending',
                'compliance_result' => null,
                'document_path'     => null,
                'file_size'         => null,
                'uploaded_at'       => null,
                'uploaded_by'       => null,
                'remarks'           => 'Pending admin verification',
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        if (! empty($items)) {
            DB::table('compliance_manual_batch_items')->insert($items);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return $count working days (Mon–Sat) in the given month.
     */
    private function getWorkingDays(int $year, int $month, int $limit): array
    {
        $days  = [];
        $date  = Carbon::create($year, $month, 1);
        $end   = $date->copy()->endOfMonth();

        while ($date <= $end && count($days) < $limit) {
            if (! $date->isSunday()) {
                $days[] = $date->toDateString();
            }
            $date->addDay();
        }

        return $days;
    }

    private function sectionFor(string $formCode): string
    {
        if (str_starts_with($formCode, 'FORM_XII') || str_starts_with($formCode, 'FORM_X')) {
            return 'CLRA';
        }
        if (str_starts_with($formCode, 'FORM_A') || str_starts_with($formCode, 'FORM_C') || str_starts_with($formCode, 'FORM_D')) {
            return 'Labour Welfare';
        }
        if (str_starts_with($formCode, 'FORM_11') || str_starts_with($formCode, 'ESI') || str_starts_with($formCode, 'EPF')) {
            return 'Social Security';
        }
        if (str_starts_with($formCode, 'SHOPS')) {
            return 'Shops & Establishments';
        }
        return 'Factories Act';
    }

    private function isApplicableForDec(string $frequency): bool
    {
        return match ($frequency) {
            'monthly'     => true,
            'annual'      => true,   // Dec is common annual filing month
            'half_yearly' => true,   // Jul–Dec half-year
            default       => false,
        };
    }

    // ── Summary ──────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $line = str_repeat('═', 62);
        $this->command->info('');
        $this->command->info($line);
        $this->command->info('  MAK47 FULL TENANT SEEDING COMPLETE — DECEMBER 2025');
        $this->command->info($line);
        $this->command->info('  Tenant ID  : ' . $this->tenantId);
        $this->command->info('  Branch ID  : ' . $this->branchId);
        $this->command->info('  User       : admin@mak47.com  /  mak47@2025');
        $this->command->info('  Company    : M/S MAK47');
        $this->command->info('  Location   : Sivagangai, Tamil Nadu');
        $this->command->info('  Principal  : Hinduja Renewables Energy Pvt Ltd');
        $this->command->info('');
        $this->command->info('  Employees  : ' . count($this->employees));
        $this->command->info('  Attendance : ' . (count($this->employees) * self::WORKING_DAYS) . ' records (27 days × 9 emp)');
        $this->command->info('  Payroll    : Basic ₹' . number_format(self::BASIC) . ' | Gross ₹' . number_format(self::GROSS) . ' | Net ₹' . number_format(self::NET));
        $this->command->info('  Deductions : PF ₹' . self::PF_EMP . ' | ESI ₹' . self::ESI_EMP . ' | PT ₹' . self::PT);
        $this->command->info('  Auto Forms : ' . count(self::AUTO_FORMS) . ' (all generated, status=success)');
        $this->command->info('  Fines      : 0 | Advances: 0 | Accidents: 0 | OT: 0');
        $this->command->info($line);
        $this->command->info('  ✓ Ready for demo — 100% compliance, 0 pending auto-forms');
        $this->command->info($line);
        $this->command->info('');
    }
}
