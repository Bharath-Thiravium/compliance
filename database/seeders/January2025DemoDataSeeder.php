<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class January2025DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;
        $branchId = 1;
        $month = 1;
        $year = 2025;

        echo "\n=== JANUARY 2025 DEMO DATA SEEDING ===\n";

        $this->seedPayrollEntries($tenantId, $branchId, $month, $year);
        $this->seedContractLabour($tenantId, $branchId, $month, $year);
        $this->seedBonusRecords($tenantId, $branchId, $month, $year);
        $this->seedIncidents($tenantId, $branchId, $month, $year);

        echo "\n✓ January 2025 demo data seeded successfully!\n";
    }

    private function seedPayrollEntries($tenantId, $branchId, $month, $year): void
    {
        echo "\n📊 Creating January 2025 Payroll Entries...";

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(25)
            ->pluck('id');

        // Get or create payroll cycle for January 2025
        $payrollCycle = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->where('cycle_name', 'January 2025')
            ->first();

        if (!$payrollCycle) {
            $periodFrom = Carbon::create($year, $month, 1)->startOfMonth();
            $periodTo = Carbon::create($year, $month, 1)->endOfMonth();

            $payrollCycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                'tenant_id' => $tenantId,
                'cycle_name' => 'January 2025',
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'status' => 'processed',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $payrollCycleId = $payrollCycle->id;
        }

        $payrollData = [];
        $count = 0;

        foreach ($employees as $employeeId) {
            $totalDaysWorked = rand(20, 26);
            $paidLeaveDays = rand(0, 2);
            $unpaidLeaveDays = 26 - $totalDaysWorked - $paidLeaveDays;
            $overtimeHours = rand(0, 20);
            
            $basicEarned = rand(15000, 50000);
            $daEarned = $basicEarned * 0.12;
            $hraEarned = $basicEarned * 0.10;
            $otherAllowances = rand(0, 5000);
            $overtimeWages = $overtimeHours * 100;
            
            $grossSalary = $basicEarned + $daEarned + $hraEarned + $otherAllowances + $overtimeWages;
            $pfEmployee = $basicEarned * 0.12;
            $esiEmployee = $grossSalary * 0.0075;
            $professionalTax = $grossSalary > 15000 ? 200 : 0;
            $fines = rand(0, 1000);
            $advances = rand(0, 5000);
            $otherDeductions = rand(0, 2000);
            
            $totalDeductions = $pfEmployee + $esiEmployee + $professionalTax + $fines + $advances + $otherDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            $payrollData[] = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $employeeId,
                'payroll_cycle_id' => $payrollCycleId,
                'total_days_worked' => $totalDaysWorked,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'overtime_hours' => $overtimeHours,
                'basic_earned' => round($basicEarned, 2),
                'da_earned' => round($daEarned, 2),
                'hra_earned' => round($hraEarned, 2),
                'other_allowances' => round($otherAllowances, 2),
                'overtime_wages' => round($overtimeWages, 2),
                'gross_salary' => round($grossSalary, 2),
                'pf_employee' => round($pfEmployee, 2),
                'esi_employee' => round($esiEmployee, 2),
                'professional_tax' => round($professionalTax, 2),
                'fines' => round($fines, 2),
                'advances' => round($advances, 2),
                'other_deductions' => round($otherDeductions, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_salary' => round($netSalary, 2),
                'payment_date' => Carbon::create($year, $month, 31),
                'payment_mode' => 'Bank Transfer',
                'transaction_reference' => 'JAN2025-' . str_pad($employeeId, 5, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $count++;
        }

        DB::table('workforce_payroll_entry')->insertOrIgnore($payrollData);
        echo " ✓ Created " . count($payrollData) . " payroll entries for January 2025";
    }

    private function seedContractLabour($tenantId, $branchId, $month, $year): void
    {
        echo "\n👷 Creating January 2025 Contract Labour Data...";

        $contractors = DB::table('contractors')
            ->where('tenant_id', $tenantId)
            ->limit(3)
            ->pluck('id');

        if ($contractors->isEmpty()) {
            echo " (Creating demo contractors)";
            
            for ($i = 0; $i < 3; $i++) {
                $contractorId = DB::table('contractors')->insertGetId([
                    'tenant_id' => $tenantId,
                    'contractor_name' => 'Contractor ' . ($i + 1),
                    'license_number' => 'LIC' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                    'valid_from' => Carbon::now()->subYear(),
                    'valid_to' => Carbon::now()->addYear(),
                    'max_worker_limit' => 50,
                    'pf_code' => 'PF' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'esi_code' => 'ESI' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $contractors->push($contractorId);
            }
        }

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(15)
            ->pluck('id');

        $contractLabourData = [];
        $count = 0;
        $deploymentLocations = ['Assembly Line A', 'Assembly Line B', 'Welding Section', 'Quality Check', 'Packaging Area'];

        foreach ($contractors as $contractorId) {
            foreach ($employees->slice($count, 5) as $employeeId) {
                $employmentStart = Carbon::create($year, $month, rand(1, 5));
                
                $contractLabourData[] = [
                    'tenant_id' => $tenantId,
                    'contractor_id' => $contractorId,
                    'employee_id' => $employeeId,
                    'deployment_location' => $deploymentLocations[rand(0, 4)],
                    'wage_rate' => rand(400, 900),
                    'employment_start' => $employmentStart,
                    'employment_end' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
        }

        DB::table('contract_labour')->insertOrIgnore($contractLabourData);
        echo " ✓ Created " . count($contractLabourData) . " contract labour records for January 2025";
    }

    private function seedBonusRecords($tenantId, $branchId, $month, $year): void
    {
        echo "\n💰 Creating January 2025 Bonus Records...";

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(25)
            ->pluck('id');

        $bonusData = [];
        $financialYear = '2024-2025';
        $bonusTypes = ['New Year Bonus', 'Performance Bonus', 'Attendance Bonus', 'Safety Bonus'];
        
        foreach ($employees as $employeeId) {
            $bonusData[] = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $employeeId,
                'financial_year' => $financialYear,
                'bonus_percentage' => rand(50, 150),
                'bonus_amount' => rand(8000, 20000),
                'payment_date' => Carbon::create($year, $month, 31),
                'status' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('bonus_records')->insertOrIgnore($bonusData);
        echo " ✓ Created " . count($bonusData) . " bonus records for January 2025";
    }

    private function seedIncidents($tenantId, $branchId, $month, $year): void
    {
        echo "\n⚠️  Creating January 2025 Incident Records...";

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(8)
            ->pluck('id');

        $incidentData = [];
        $incidentTypes = [
            'Minor Injury',
            'Lost Time Injury',
            'Dangerous Occurrence',
            'Near Miss',
            'Occupational Disease',
            'Burn Injury',
            'Cut/Laceration',
            'Strain/Sprain'
        ];
        $locations = ['Assembly Line A', 'Assembly Line B', 'Welding Section', 'Quality Check', 'Packaging Area', 'Storage Area', 'Office Area'];
        $count = 0;

        foreach ($employees as $employeeId) {
            // Each employee has 2-3 incidents
            $incidentCount = rand(2, 3);
            for ($i = 0; $i < $incidentCount; $i++) {
                $incidentDate = Carbon::create($year, $month, rand(1, 28));
                $incidentData[] = [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $employeeId,
                    'incident_type' => $incidentTypes[rand(0, 7)],
                    'incident_date' => $incidentDate,
                    'location' => $locations[rand(0, 6)],
                    'description' => 'Incident occurred during work shift. Employee ' . $employeeId . ' reported incident on ' . $incidentDate->format('Y-m-d'),
                    'authority_name' => 'Factory Inspector - ' . ['Mr. Sharma', 'Ms. Patel', 'Mr. Kumar', 'Ms. Singh'][rand(0, 3)],
                    'reference_number' => 'JAN2025-INC-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
                    'uploaded_by' => 1,
                    'uploaded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
        }

        DB::table('incident_documents')->insertOrIgnore($incidentData);
        echo " ✓ Created " . count($incidentData) . " incident records for January 2025";
    }
}
