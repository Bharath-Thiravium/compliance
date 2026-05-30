<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FillMissingDataSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve IDs dynamically — don't assume 1
        $tenant = DB::table('tenants')->orderBy('id')->first();
        $branch = DB::table('branches')->orderBy('id')->first();

        if (!$tenant || !$branch) {
            $this->command->error('No tenant/branch found — run January2025ComprehensiveSeeder first.');
            return;
        }

        $tenantId = $tenant->id;
        $branchId = $branch->id;

        $this->command->info("Filling missing data for Tenant: {$tenantId}, Branch: {$branchId}");

        // Get payroll cycle for January 2025
        $payrollCycle = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->where('cycle_name', 'January 2025')
            ->first();

        if (!$payrollCycle) {
            $this->command->error("Payroll cycle not found!");
            return;
        }

        // Get all employees
        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->get();

        if ($employees->isEmpty()) {
            $this->command->error("No employees found!");
            return;
        }

        $this->command->info("Found " . $employees->count() . " employees");

        // Fill Payroll Entries
        $this->fillPayrollEntries($tenantId, $branchId, $payrollCycle, $employees);

        // Fill Bonus Records
        $this->fillBonusRecords($tenantId, $branchId, $employees);

        // Fill Incidents
        $this->fillIncidents($tenantId, $branchId, $employees);

        $this->command->info("✓ All missing data filled successfully!");
    }

    private function fillPayrollEntries($tenantId, $branchId, $payrollCycle, $employees): void
    {
        // Delete existing payroll entries
        DB::table('workforce_payroll_entry')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('payroll_cycle_id', $payrollCycle->id)
            ->delete();

        $totalRecords = 0;

        foreach ($employees as $emp) {
            $totalDaysWorked = rand(22, 26);
            $paidLeaveDays = rand(0, 2);
            $unpaidLeaveDays = rand(0, 1);
            $overtimeHours = rand(0, 3) * 2;

            $basicEarned = ($emp->basic_salary / 26) * $totalDaysWorked;
            $daEarned = ($basicEarned * 0.15);
            $hraEarned = ($basicEarned * 0.10);
            $otherAllowances = rand(500, 2000);
            $overtimeWages = ($basicEarned / 8) * $overtimeHours;
            $grossSalary = $basicEarned + $daEarned + $hraEarned + $otherAllowances + $overtimeWages;

            $pfEmployee = ($basicEarned * 0.12);
            $esiEmployee = ($basicEarned * 0.0175);
            $professionalTax = $basicEarned > 15000 ? 200 : 0;
            $fines = rand(0, 1) === 1 ? rand(100, 500) : 0;
            $advances = rand(0, 1) === 1 ? rand(1000, 5000) : 0;
            $otherDeductions = 0;

            $totalDeductions = $pfEmployee + $esiEmployee + $professionalTax + $fines + $advances + $otherDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            DB::table('workforce_payroll_entry')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'payroll_cycle_id' => $payrollCycle->id,
                'employee_id' => $emp->id,
                'total_days_worked' => $totalDaysWorked,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'overtime_hours' => $overtimeHours,
                'basic_earned' => round($basicEarned, 2),
                'da_earned' => round($daEarned, 2),
                'hra_earned' => round($hraEarned, 2),
                'other_allowances' => $otherAllowances,
                'overtime_wages' => round($overtimeWages, 2),
                'gross_salary' => round($grossSalary, 2),
                'pf_employee' => round($pfEmployee, 2),
                'esi_employee' => round($esiEmployee, 2),
                'professional_tax' => $professionalTax,
                'fines' => $fines,
                'advances' => $advances,
                'other_deductions' => $otherDeductions,
                'total_deductions' => round($totalDeductions, 2),
                'net_salary' => round($netSalary, 2),
                'payment_date' => Carbon::parse($payrollCycle->period_to)->addDays(5),
                'payment_mode' => 'Bank Transfer',
                'transaction_reference' => 'TXN/' . $emp->employee_code . '/202501',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalRecords++;
        }

        $this->command->info("✓ Created {$totalRecords} Payroll Entries");
    }

    private function fillBonusRecords($tenantId, $branchId, $employees): void
    {
        // Delete existing bonus records
        DB::table('bonus_records')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->delete();

        $totalRecords = 0;

        foreach ($employees as $emp) {
            $totalSalaryForYear = $emp->basic_salary * 12;
            $bonusPercentage = 8.33;
            $bonusAmount = ($totalSalaryForYear * $bonusPercentage) / 100;

            DB::table('bonus_records')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $emp->id,
                'financial_year' => '2024-2025',
                'bonus_percentage' => $bonusPercentage,
                'bonus_amount' => round($bonusAmount, 2),
                'payment_date' => Carbon::create(2025, 3, 31),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalRecords++;
        }

        $this->command->info("✓ Created {$totalRecords} Bonus Records");
    }

    private function fillIncidents($tenantId, $branchId, $employees): void
    {
        // Delete existing incidents
        DB::table('incidents')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->delete();

        $hazardTypes = ['Cut injury', 'Slip and fall', 'Burn injury', 'Fracture', 'Strain injury', 'Chemical burn', 'Eye injury', 'Contusion'];
        $locations = ['Production Floor', 'Maintenance Area', 'Storage Room', 'Electrical Room', 'Packaging Area', 'Quality Lab', 'Assembly Line', 'Warehouse'];
        $causes = ['Machinery malfunction', 'Wet floor', 'Improper handling', 'Lack of PPE', 'Electrical fault', 'Chemical spill', 'Poor lighting', 'Unsafe practice'];
        $activities = ['Operating machinery', 'Lifting materials', 'Cleaning', 'Maintenance work', 'Packaging', 'Quality check', 'Material handling', 'Assembly work'];

        $totalRecords = 0;

        // Create 15 incident records spread across January 2025
        for ($i = 0; $i < 15; $i++) {
            $emp = $employees[$i % $employees->count()];
            $incidentDay = rand(1, 28);
            $noticeDay = min($incidentDay + rand(1, 3), 31);

            DB::table('incidents')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $emp->id,
                'notice_date' => Carbon::create(2025, 1, $noticeDay),
                'notice_time' => rand(8, 17) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                'incident_date' => Carbon::create(2025, 1, $incidentDay),
                'incident_time' => rand(8, 17) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                'location' => $locations[$i % count($locations)],
                'cause' => $causes[$i % count($causes)],
                'injury_type' => $hazardTypes[$i % count($hazardTypes)],
                'activity' => $activities[$i % count($activities)],
                'first_aid_by' => 'Safety Officer - ' . chr(65 + ($i % 3)),
                'witness' => 'Supervisor and ' . $employees[($i + 1) % $employees->count()]->name,
                'remarks' => 'Incident reported and documented. First aid provided. Employee fit to work.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalRecords++;
        }

        $this->command->info("✓ Created {$totalRecords} Incident Records");
    }
}
