<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class FreshComplianceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Starting fresh compliance data seeding...');
        
        // Clear existing demo data
        $this->clearDemoData();
        
        // Create fresh data
        $userId = $this->createUser();
        $tenantId = $this->createTenant();
        $branchId = $this->createBranch($tenantId);
        $payrollCycles = $this->createPayrollCycles($tenantId);
        $employees = $this->createEmployees($tenantId, $branchId);
        $this->createPayrollEntries($tenantId, $branchId, $payrollCycles, $employees);
        $this->createBonusRecords($tenantId, $branchId, $employees);
        $contractorId = $this->createContractor($tenantId);
        $complianceId = $this->createContractorCompliance($contractorId, $branchId);
        $this->createContractLabourDeployment($tenantId, $contractorId, $complianceId, $branchId, $employees);
        $this->createIncidents($tenantId, $branchId, $employees, $userId);
        
        $this->printSummary($tenantId, $branchId, count($employees), count($payrollCycles));
    }

    private function clearDemoData(): void
    {
        $this->command->info('🗑️  Clearing existing demo data...');
        
        DB::statement('PRAGMA foreign_keys = OFF');
        
        DB::table('contract_labour_deployment')->truncate();
        DB::table('contractor_compliance')->truncate();
        DB::table('contractor_master')->truncate();
        DB::table('incident_documents')->truncate();
        DB::table('bonus_records')->truncate();
        DB::table('workforce_payroll_entry')->truncate();
        DB::table('workforce_payroll_cycle')->truncate();
        DB::table('workforce_employee')->truncate();
        DB::table('branches')->truncate();
        DB::table('tenants')->truncate();
        DB::table('users')->truncate();
        
        DB::statement('PRAGMA foreign_keys = ON');
        
        $this->command->info('✓ Demo data cleared');
    }

    private function createUser(): int
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@compliance.local',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created User: {$userId}");
        return $userId;
    }

    private function createTenant(): int
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Demo Compliance Industries Pvt Ltd',
            'subscription_type' => 'FULL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created Tenant: {$tenantId}");
        return $tenantId;
    }

    private function createBranch(int $tenantId): int
    {
        $branchId = DB::table('branches')->insertGetId([
            'tenant_id' => $tenantId,
            'branch_name' => 'Solar Panel Manufacturing Unit',
            'factory_license_number' => 'TN/FAC/2025/001',
            'address' => 'No.53 Nungambakkam High Road, Chennai – 600034',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created Branch: {$branchId}");
        return $branchId;
    }

    private function createPayrollCycles(int $tenantId): array
    {
        $months = [
            ['name' => 'January 2025', 'from' => '2025-01-01', 'to' => '2025-01-31'],
            ['name' => 'February 2025', 'from' => '2025-02-01', 'to' => '2025-02-28'],
            ['name' => 'March 2025', 'from' => '2025-03-01', 'to' => '2025-03-31'],
        ];

        $cycles = [];
        foreach ($months as $month) {
            $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                'tenant_id' => $tenantId,
                'cycle_name' => $month['name'],
                'period_from' => $month['from'],
                'period_to' => $month['to'],
                'status' => 'processed',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cycles[] = [
                'id' => $cycleId,
                'name' => $month['name'],
                'from' => $month['from'],
                'to' => $month['to'],
            ];
        }

        $this->command->info("✓ Created " . count($cycles) . " Payroll Cycles");
        return $cycles;
    }

    private function createEmployees(int $tenantId, int $branchId): array
    {
        $departments = ['Production', 'Maintenance', 'Quality', 'Packaging', 'Safety'];
        $designations = ['Supervisor', 'Technician', 'Machine Operator', 'Helper', 'Electrician', 'Safety Officer'];
        $firstNames = ['Raj', 'Kumar', 'Vijay', 'Arun', 'Suresh', 'Ramesh', 'Ganesh', 'Prakash', 'Dinesh', 'Mahesh', 'Priya', 'Anjali', 'Divya', 'Neha', 'Pooja'];
        $lastNames = ['Kumar', 'Raj', 'Prasad', 'Reddy', 'Sharma', 'Singh', 'Patel', 'Gupta', 'Verma', 'Rao'];

        $employees = [];
        for ($i = 1; $i <= 25; $i++) {
            $empCode = 'T' . $tenantId . 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            $department = $departments[($i - 1) % count($departments)];
            $designation = $designations[($i - 1) % count($designations)];
            $joiningDate = Carbon::create(2024, rand(6, 12), rand(1, 28));
            $basicSalary = match ($designation) {
                'Supervisor' => 35000,
                'Technician' => 25000,
                'Machine Operator' => 20000,
                'Electrician' => 28000,
                'Safety Officer' => 26000,
                default => 18000,
            };

            $employeeId = DB::table('workforce_employee')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_code' => $empCode,
                'name' => $fullName,
                'pf_number' => 'PF/TN/2025/' . $empCode,
                'esi_number' => 'ESI/TN/2025/' . $empCode,
                'date_of_joining' => $joiningDate,
                'designation' => $designation,
                'department' => $department,
                'basic_salary' => $basicSalary,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employees[] = [
                'id' => $employeeId,
                'code' => $empCode,
                'name' => $fullName,
                'basic_salary' => $basicSalary,
                'designation' => $designation,
                'department' => $department,
            ];
        }

        $this->command->info("✓ Created " . count($employees) . " Employees");
        return $employees;
    }

    private function createPayrollEntries(int $tenantId, int $branchId, array $cycles, array $employees): void
    {
        $totalRecords = 0;

        foreach ($cycles as $cycle) {
            foreach ($employees as $emp) {
                $totalDaysWorked = rand(22, 26);
                $paidLeaveDays = rand(0, 2);
                $unpaidLeaveDays = rand(0, 1);
                $overtimeHours = rand(0, 3) * 2;

                $basicEarned = ($emp['basic_salary'] / 26) * $totalDaysWorked;
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
                    'payroll_cycle_id' => $cycle['id'],
                    'employee_id' => $emp['id'],
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
                    'payment_date' => Carbon::parse($cycle['to'])->addDays(5),
                    'payment_mode' => 'Bank Transfer',
                    'transaction_reference' => 'TXN/' . $emp['code'] . '/' . date('Ym', strtotime($cycle['from'])),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalRecords++;
            }
        }

        $this->command->info("✓ Created {$totalRecords} Payroll Entries");
    }

    private function createBonusRecords(int $tenantId, int $branchId, array $employees): void
    {
        foreach ($employees as $emp) {
            $totalSalaryForYear = $emp['basic_salary'] * 12;
            $bonusPercentage = 8.33;
            $bonusAmount = ($totalSalaryForYear * $bonusPercentage) / 100;

            DB::table('bonus_records')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $emp['id'],
                'financial_year' => '2024-2025',
                'bonus_percentage' => $bonusPercentage,
                'bonus_amount' => round($bonusAmount, 2),
                'payment_date' => Carbon::create(2025, 3, 31),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("✓ Created " . count($employees) . " Bonus Records");
    }

    private function createContractor(int $tenantId): int
    {
        $contractorId = DB::table('contractor_master')->insertGetId([
            'tenant_id' => $tenantId,
            'company_type' => 'Manpower',
            'company_name' => 'GIRI Manpower Services',
            'license_number' => 'LIC/GIRI/2025/001',
            'valid_from' => Carbon::create(2025, 1, 1),
            'valid_to' => Carbon::create(2026, 12, 31),
            'max_worker_limit' => 50,
            'company_address' => 'Chennai, Tamil Nadu',
            'contact_person' => 'Mr. Rajesh Kumar',
            'contact_number' => '9876543210',
            'email' => 'contact@girimanpower.com',
            'pan_number' => 'AABCT1234A',
            'gst_number' => '33AABCT1234A1Z0',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created Contractor: {$contractorId}");
        return $contractorId;
    }

    private function createContractorCompliance(int $contractorId, int $branchId): int
    {
        $complianceId = DB::table('contractor_compliance')->insertGetId([
            'contractor_id' => $contractorId,
            'branch_id' => $branchId,
            'clra_license_number' => 'CLRA-TN-2025-001',
            'license_valid_from' => Carbon::create(2025, 1, 1),
            'license_valid_to' => Carbon::create(2026, 12, 31),
            'max_worker_limit' => 50,
            'pf_code' => 'PF/GIRI/2025',
            'esi_code' => 'ESI/GIRI/2025',
            'labour_registration_number' => 'LR/TN/2025/001',
            'last_return_filed' => Carbon::create(2025, 1, 15),
            'is_compliant' => true,
            'compliance_notes' => 'All documents verified and compliant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created Contractor Compliance: {$complianceId}");
        return $complianceId;
    }

    private function createContractLabourDeployment(int $tenantId, int $contractorId, int $complianceId, int $branchId, array $employees): void
    {
        for ($i = 0; $i < 10; $i++) {
            $empIndex = $i % count($employees);
            $emp = $employees[$empIndex];

            DB::table('contract_labour_deployment')->insert([
                'tenant_id' => $tenantId,
                'contractor_id' => $contractorId,
                'contractor_compliance_id' => $complianceId,
                'employee_id' => $emp['id'],
                'branch_id' => $branchId,
                'wage_rate' => $emp['basic_salary'],
                'deployment_start' => Carbon::create(2025, 1, 1),
                'deployment_end' => Carbon::create(2025, 12, 31),
                'work_order_number' => 'WO/GIRI/2025/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'work_order_date' => Carbon::create(2024, 12, 15),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("✓ Created 10 Contract Labour Deployments");
    }

    private function createIncidents(int $tenantId, int $branchId, array $employees, int $userId): void
    {
        for ($i = 0; $i < 2; $i++) {
            $emp = $employees[$i];

            DB::table('incident_documents')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $emp['id'],
                'incident_type' => 'accident',
                'incident_date' => Carbon::create(2025, rand(1, 3), rand(1, 28)),
                'location' => 'Production Floor - Section ' . chr(65 + $i),
                'description' => $i === 0
                    ? 'Minor cut injury while operating machinery'
                    : 'Slip and fall on wet floor',
                'authority_name' => 'Factory Inspector',
                'reference_number' => 'ACC/TN/2025/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'document_path' => '/incidents/accident_' . ($i + 1) . '.pdf',
                'uploaded_by' => $userId,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('incident_documents')->insert([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'employee_id' => null,
            'incident_type' => 'dangerous',
            'incident_date' => Carbon::create(2025, 2, 15),
            'location' => 'Maintenance Department',
            'description' => 'Boiler pressure leak detected during routine inspection',
            'authority_name' => 'Factory Inspector',
            'reference_number' => 'DNG/TN/2025/001',
            'document_path' => '/incidents/dangerous_occurrence_1.pdf',
            'uploaded_by' => $userId,
            'uploaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created 3 Incident Records");
    }

    private function printSummary(int $tenantId, int $branchId, int $employeeCount, int $cycleCount): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('  ✅ FRESH COMPLIANCE DATA SEEDING COMPLETE');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('TENANT INFORMATION:');
        $this->command->info('  Company: Demo Compliance Industries Pvt Ltd');
        $this->command->info('  Tenant ID: ' . $tenantId);
        $this->command->info('  Branch: Solar Panel Manufacturing Unit');
        $this->command->info('  Branch ID: ' . $branchId);
        $this->command->info('');
        $this->command->info('RECORDS CREATED:');
        $this->command->info('  Employees: ' . $employeeCount);
        $this->command->info('  Payroll Cycles: ' . $cycleCount);
        $this->command->info('  Payroll Entries: ' . ($employeeCount * $cycleCount));
        $this->command->info('  Bonus Records: ' . $employeeCount);
        $this->command->info('  Contractors: 1');
        $this->command->info('  Contract Labour Deployments: 10');
        $this->command->info('  Incident Records: 3');
        $this->command->info('');
        $this->command->info('✓ All forms can now be generated with realistic data');
        $this->command->info('═══════════════════════════════════════════════════════════════');
    }
}
