<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComprehensiveDemoDataSeeder2 extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;
        $branchId = 1;

        echo "\n=== COMPREHENSIVE DEMO DATA SEEDING ===\n";

        $this->seedPayrollEntries($tenantId, $branchId);
        $this->seedContractLabour($tenantId, $branchId);
        $this->seedBonusRecords($tenantId, $branchId);
        $this->seedIncidents($tenantId, $branchId);
        $this->seedHazardRegister($tenantId, $branchId);

        echo "\n✓ All demo data seeded successfully!\n";
    }

    private function seedPayrollEntries($tenantId, $branchId): void
    {
        echo "\n📊 Creating Payroll Entries...";

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(25)
            ->pluck('id');

        $payrollCycle = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->orderBy('period_from', 'desc')
            ->first();

        if (!$payrollCycle) {
            $periodFrom = Carbon::now()->startOfMonth();
            $periodTo = Carbon::now()->endOfMonth();

            $payrollCycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                'tenant_id' => $tenantId,
                'cycle_name' => $periodFrom->format('F Y'),
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'status' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $payrollCycleId = $payrollCycle->id;
        }

        $payrollData = [];
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
                'payment_date' => Carbon::now(),
                'payment_mode' => 'Bank Transfer',
                'transaction_reference' => 'TXN' . str_pad($employeeId, 8, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('workforce_payroll_entry')->insertOrIgnore($payrollData);
        echo " ✓ Created " . count($payrollData) . " payroll entries";
    }

    private function seedContractLabour($tenantId, $branchId): void
    {
        echo "\n👷 Creating Contract Labour Data...";

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

        foreach ($contractors as $contractorId) {
            foreach ($employees->slice($count, 5) as $employeeId) {
                $contractLabourData[] = [
                    'tenant_id' => $tenantId,
                    'contractor_id' => $contractorId,
                    'employee_id' => $employeeId,
                    'deployment_location' => 'Work Area ' . rand(1, 5),
                    'wage_rate' => rand(300, 800),
                    'employment_start' => Carbon::now()->subMonths(rand(1, 12)),
                    'employment_end' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
        }

        DB::table('contract_labour')->insertOrIgnore($contractLabourData);
        echo " ✓ Created " . count($contractLabourData) . " contract labour records";
    }

    private function seedBonusRecords($tenantId, $branchId): void
    {
        echo "\n💰 Creating Bonus Records...";

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(25)
            ->pluck('id');

        $bonusData = [];
        $financialYear = date('Y') . '-' . (date('Y') + 1);
        
        foreach ($employees as $employeeId) {
            $bonusData[] = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $employeeId,
                'financial_year' => $financialYear,
                'bonus_percentage' => rand(50, 100),
                'bonus_amount' => rand(5000, 15000),
                'payment_date' => Carbon::now(),
                'status' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('bonus_records')->insertOrIgnore($bonusData);
        echo " ✓ Created " . count($bonusData) . " bonus records";
    }

    private function seedIncidents($tenantId, $branchId): void
    {
        echo "\n⚠️  Creating Incident Records...";

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->limit(5)
            ->pluck('id');

        $incidentData = [];
        $incidentTypes = ['Minor Injury', 'Lost Time Injury', 'Dangerous Occurrence', 'Near Miss', 'Occupational Disease'];
        $count = 0;

        foreach ($employees as $employeeId) {
            for ($i = 0; $i < 2; $i++) {
                $incidentDate = Carbon::now()->subDays(rand(1, 30));
                $incidentData[] = [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $employeeId,
                    'incident_type' => $incidentTypes[rand(0, 4)],
                    'incident_date' => $incidentDate,
                    'location' => 'Work Area ' . rand(1, 5),
                    'description' => 'Incident description for record ' . ($count + 1),
                    'authority_name' => 'Factory Inspector',
                    'reference_number' => 'INC' . str_pad($count + 1, 5, '0', STR_PAD_LEFT),
                    'uploaded_by' => 1,
                    'uploaded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
        }

        DB::table('incident_documents')->insertOrIgnore($incidentData);
        echo " ✓ Created " . count($incidentData) . " incident records";
    }

    private function seedHazardRegister($tenantId, $branchId): void
    {
        echo "\n🔴 Creating Hazard Register...";

        $hazards = [
            ['Chemical Exposure', 'High', 'Chemical storage area'],
            ['Noise Pollution', 'Medium', 'Manufacturing floor'],
            ['Electrical Hazard', 'High', 'Electrical room'],
            ['Fire Risk', 'High', 'Storage area'],
            ['Slipping Hazard', 'Low', 'Wet floor areas'],
            ['Heavy Machinery', 'High', 'Production area'],
            ['Heat Stress', 'Medium', 'Furnace area'],
            ['Dust Inhalation', 'Medium', 'Grinding section'],
            ['Ergonomic Risk', 'Low', 'Office area'],
            ['Biological Hazard', 'Medium', 'Waste disposal area'],
        ];

        $hazardData = [];
        foreach ($hazards as $index => $hazard) {
            $hazardData[] = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'hazard_date' => Carbon::now()->subDays(rand(1, 30)),
                'hazard_type' => $hazard[0],
                'description' => 'Hazard description for ' . $hazard[0],
                'location' => $hazard[2],
                'severity' => $hazard[1],
                'status' => 'active',
                'corrective_action' => 'Corrective action for ' . $hazard[0],
                'action_date' => Carbon::now()->addDays(rand(1, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('hazard_register')->insertOrIgnore($hazardData);
        echo " ✓ Created " . count($hazardData) . " hazard records";
    }
}
