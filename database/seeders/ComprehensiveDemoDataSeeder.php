<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ComprehensiveDemoDataSeeder extends Seeder
{
    private int $tenantId;
    private int $branchId;
    private array $employees = [];
    private array $payrollCycles = [];
    private int $contractorId;
    private int $complianceId;
    private int $userId;

    public function run(): void
    {
        // Check if user already exists
        if (!DB::table('users')->where('email', 'admin@demo.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->createTenant();
        $this->createBranch();
        $this->createPayrollCycles();
        $this->createEmployees();
        $this->createPayrollEntries();
        $this->createBonusRecords();
        $this->createContractor();
        $this->createContractLabourDeployment();
        $this->createIncidents();
        $this->printSummary();
    }

    private function createTenant(): void
    {
        $this->tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Demo Compliance Industries Pvt Ltd',
            'subscription_type' => 'FULL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')
            ->where('email', 'admin@demo.com')
            ->update(['tenant_id' => $this->tenantId]);

        $this->command->info("✓ Created Tenant: {$this->tenantId}");
    }

    private function createBranch(): void
    {
        $this->branchId = DB::table('branches')->insertGetId([
            'tenant_id' => $this->tenantId,
            'branch_name' => 'Solar Panel Manufacturing Unit',
            'factory_license_number' => 'TN/FAC/2025/001',
            'address' => 'No.53 Nungambakkam High Road, Chennai – 600034',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created Branch: {$this->branchId}");
    }

    private function createPayrollCycles(): void
    {
        $months = [
            ['name' => 'January 2025', 'from' => '2025-01-01', 'to' => '2025-01-31'],
            ['name' => 'February 2025', 'from' => '2025-02-01', 'to' => '2025-02-28'],
            ['name' => 'March 2025', 'from' => '2025-03-01', 'to' => '2025-03-31'],
        ];

        foreach ($months as $month) {
            $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                'tenant_id' => $this->tenantId,
                'cycle_name' => $month['name'],
                'period_from' => $month['from'],
                'period_to' => $month['to'],
                'status' => 'processed',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->payrollCycles[] = [
                'id' => $cycleId,
                'name' => $month['name'],
                'from' => $month['from'],
                'to' => $month['to'],
            ];
        }

        $this->command->info("✓ Created " . count($this->payrollCycles) . " Payroll Cycles");
    }

    private function createEmployees(): void
    {
        $departments = ['Production', 'Maintenance', 'Quality', 'Packaging', 'Safety'];
        $designations = ['Supervisor', 'Technician', 'Machine Operator', 'Helper', 'Electrician', 'Safety Officer'];
        $firstNames = ['Raj', 'Kumar', 'Vijay', 'Arun', 'Suresh', 'Ramesh', 'Ganesh', 'Prakash', 'Dinesh', 'Mahesh', 'Priya', 'Anjali', 'Divya', 'Neha', 'Pooja'];
        $lastNames = ['Kumar', 'Raj', 'Prasad', 'Reddy', 'Sharma', 'Singh', 'Patel', 'Gupta', 'Verma', 'Rao'];

        for ($i = 1; $i <= 25; $i++) {
            $empCode = 'T' . $this->tenantId . 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
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
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
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

            $this->employees[] = [
                'id' => $employeeId,
                'code' => $empCode,
                'name' => $fullName,
                'basic_salary' => $basicSalary,
                'designation' => $designation,
                'department' => $department,
            ];
        }

        $this->command->info("✓ Created " . count($this->employees) . " Employees");
    }

    private function createPayrollEntries(): void
    {
        $totalRecords = 0;

        foreach ($this->payrollCycles as $cycle) {
            foreach ($this->employees as $emp) {
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
                    'tenant_id' => $this->tenantId,
                    'branch_id' => $this->branchId,
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

    private function createBonusRecords(): void
    {
        $totalRecords = 0;

        foreach ($this->employees as $emp) {
            $totalSalaryForYear = $emp['basic_salary'] * 12;
            $bonusPercentage = 8.33;
            $bonusAmount = ($totalSalaryForYear * $bonusPercentage) / 100;

            DB::table('bonus_records')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_id' => $emp['id'],
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

    private function createContractor(): void
    {
        // Create contractor_master entry
        $this->contractorId = DB::table('contractor_master')->insertGetId([
            'tenant_id' => $this->tenantId,
            'company_type' => 'Manpower',
            'company_name' => 'GIRI Manpower Services',
            'license_number' => 'LIC/GIRI/2025/001', //
            'valid_from' => Carbon::create(2025, 1, 1),     // ✅ required
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

        // Create contractor_compliance entry
        $this->complianceId = DB::table('contractor_compliance')->insertGetId([
            'contractor_id' => $this->contractorId,
            'branch_id' => $this->branchId,
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

        $this->command->info("✓ Created Contractor: {$this->contractorId} with Compliance ID: {$this->complianceId}");
    }

    private function createContractLabourDeployment(): void
    {
        $totalRecords = 0;

        for ($i = 0; $i < 10; $i++) {
            $empIndex = $i % count($this->employees);
            $emp = $this->employees[$empIndex];

            DB::table('contract_labour_deployment')->insert([
                'tenant_id' => $this->tenantId,
                'contractor_id' => $this->contractorId,
                'contractor_compliance_id' => $this->complianceId,
                'employee_id' => $emp['id'],
                'branch_id' => $this->branchId,
                'wage_rate' => $emp['basic_salary'],
                'deployment_start' => Carbon::create(2025, 1, 1),
                'deployment_end' => Carbon::create(2025, 12, 31),
                'work_order_number' => 'WO/GIRI/2025/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'work_order_date' => Carbon::create(2024, 12, 15),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalRecords++;
        }

        $this->command->info("✓ Created {$totalRecords} Contract Labour Deployments");
    }

    private function createIncidents(): void
    {
        // Get a user for uploaded_by
        $this->userId = DB::table('users')->value('id') ?? 1;

        // Create 2 accident records
        for ($i = 0; $i < 2; $i++) {
            $emp = $this->employees[$i];

            DB::table('incident_documents')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
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
                'uploaded_by' => $this->userId,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create 1 dangerous occurrence
        DB::table('incident_documents')->insert([
            'tenant_id' => $this->tenantId,
            'branch_id' => $this->branchId,
            'employee_id' => null,
            'incident_type' => 'dangerous',
            'incident_date' => Carbon::create(2025, 2, 15),
            'location' => 'Maintenance Department',
            'description' => 'Boiler pressure leak detected during routine inspection',
            'authority_name' => 'Factory Inspector',
            'reference_number' => 'DNG/TN/2025/001',
            'document_path' => '/incidents/dangerous_occurrence_1.pdf',
            'uploaded_by' => $this->userId,
            'uploaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created 3 Incident Records (2 Accidents + 1 Dangerous Occurrence)");
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('  COMPREHENSIVE DEMO DATA SEEDING COMPLETE');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('TENANT INFORMATION:');
        $this->command->info('  Company: Demo Compliance Industries Pvt Ltd');
        $this->command->info('  Tenant ID: ' . $this->tenantId);
        $this->command->info('  Branch: Solar Panel Manufacturing Unit');
        $this->command->info('  Branch ID: ' . $this->branchId);
        $this->command->info('  Location: Sriperumbudur Industrial Area');
        $this->command->info('');
        $this->command->info('RECORDS CREATED:');
        $this->command->info('  Employees: ' . count($this->employees));
        $this->command->info('  Payroll Cycles: ' . count($this->payrollCycles));
        $this->command->info('  Payroll Entries: ' . (count($this->employees) * count($this->payrollCycles)));
        $this->command->info('  Bonus Records: ' . count($this->employees));
        $this->command->info('  Contractors: 1');
        $this->command->info('  Contract Labour Deployments: 10');
        $this->command->info('  Incident Records: 3');
        $this->command->info('');
        $this->command->info('PAYROLL PERIODS:');
        foreach ($this->payrollCycles as $cycle) {
            $this->command->info('  • ' . $cycle['name']);
        }
        $this->command->info('');
        $this->command->info('✓ All forms can now be generated with realistic data');
        $this->command->info('═══════════════════════════════════════════════════════════════');
    }
}
