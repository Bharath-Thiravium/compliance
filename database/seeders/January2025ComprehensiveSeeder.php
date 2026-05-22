<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class January2025ComprehensiveSeeder extends Seeder
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
        $this->cleanData();
        $this->createUser();
        $this->createTenant();
        $this->createBranch();
        $this->createPayrollCycles();
        $this->createEmployees();
        $this->createAttendance();
        $this->createPayrollEntries();
        $this->createBonusRecords();
        $this->createContractor();
        $this->createContractLabourDeployment();
        $this->createHazardRegister();
        $this->createIncidents();
        $this->printSummary();
    }

    private function cleanData(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::table('hazard_register')->truncate();
        DB::table('incident_documents')->truncate();
        DB::table('incidents')->truncate();
        DB::table('contract_labour_deployment')->truncate();
        DB::table('contractor_compliance')->truncate();
        DB::table('contractor_master')->truncate();
        DB::table('bonus_records')->truncate();
        DB::table('workforce_payroll_entry')->truncate();
        DB::table('workforce_attendance')->truncate();
        DB::table('workforce_payroll_cycle')->truncate();
        DB::table('workforce_employee')->truncate();
        DB::table('branches')->truncate();
        DB::table('tenants')->truncate();
        DB::table('users')->truncate();
        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function createUser(): void
    {
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTenant(): void
    {
        $this->tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Demo Compliance Industries Pvt Ltd',
            'establishment_name' => 'Solar Panel Manufacturing Unit',
            'factory_license_no' => 'TN/FAC/2025/001',
            'pf_code' => 'TN/PF/2025/001',
            'esi_code' => 'TN/ESI/2025/001',
            'subscription_type' => 'FULL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $this->userId)->update(['tenant_id' => $this->tenantId]);
        $this->command->info("✓ Created Tenant: {$this->tenantId}");
    }

    private function createBranch(): void
    {
        $this->branchId = DB::table('branches')->insertGetId([
            'tenant_id' => $this->tenantId,
            'branch_name' => 'Solar Panel Manufacturing Unit',
            'unit_name' => 'Solar Panel Manufacturing Unit',
            'factory_license_number' => 'TN/FAC/2025/001',
            'address' => 'No.53 Nungambakkam High Road, Chennai – 600034',
            'pf_code' => 'TN/PF/2025/001',
            'esi_code' => 'TN/ESI/2025/001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("✓ Created Branch: {$this->branchId}");
    }

    private function createPayrollCycles(): void
    {
        $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
            'tenant_id' => $this->tenantId,
            'cycle_name' => 'January 2025',
            'period_from' => '2025-01-01',
            'period_to' => '2025-01-31',
            'status' => 'processed',
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->payrollCycles[] = [
            'id' => $cycleId,
            'name' => 'January 2025',
            'from' => '2025-01-01',
            'to' => '2025-01-31',
        ];

        $this->command->info("✓ Created Payroll Cycle: January 2025");
    }

    private function createEmployees(): void
    {
        $departments = ['Production', 'Maintenance', 'Quality', 'Packaging', 'Safety'];
        $designations = ['Supervisor', 'Technician', 'Machine Operator', 'Helper', 'Electrician', 'Safety Officer'];
        $firstNames = ['Raj', 'Kumar', 'Vijay', 'Arun', 'Suresh', 'Ramesh', 'Ganesh', 'Prakash', 'Dinesh', 'Mahesh'];
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
                'gender' => $i % 2 === 0 ? 'F' : 'M',
                'date_of_birth' => Carbon::create(1990 + rand(0, 30), rand(1, 12), rand(1, 28)),
                'permanent_address' => 'Chennai, Tamil Nadu',
                'local_address' => 'Chennai, Tamil Nadu',
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

    private function createAttendance(): void
    {
        $totalRecords = 0;
        $start = Carbon::create(2025, 1, 1);
        $end = Carbon::create(2025, 1, 31);

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($this->employees as $emp) {
                $status = rand(1, 100) <= 85 ? 'present' : (rand(1, 100) <= 50 ? 'absent' : 'leave');

                DB::table('workforce_attendance')->insert([
                    'tenant_id' => $this->tenantId,
                    'branch_id' => $this->branchId,
                    'employee_id' => $emp['id'],
                    'attendance_date' => $date->toDateString(),
                    'status' => $status,
                    'remarks' => $status === 'leave' ? 'Casual Leave' : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalRecords++;
            }
        }

        $this->command->info("✓ Created {$totalRecords} Attendance Records");
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
                    'transaction_reference' => 'TXN/' . $emp['code'] . '/202501',
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
        $this->contractorId = DB::table('contractor_master')->insertGetId([
            'tenant_id' => $this->tenantId,
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

    private function createHazardRegister(): void
    {
        $hazardTypes = ['Chemical Exposure', 'Noise Hazard', 'Electrical Hazard', 'Mechanical Hazard', 'Fire Hazard', 'Thermal Hazard'];
        $locations = ['Production Floor', 'Maintenance Area', 'Storage Room', 'Electrical Room', 'Packaging Area', 'Quality Lab'];
        $severities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['open', 'mitigated', 'closed'];

        $totalRecords = 0;

        for ($i = 0; $i < 8; $i++) {
            DB::table('hazard_register')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'hazard_date' => Carbon::create(2025, 1, rand(1, 31)),
                'hazard_type' => $hazardTypes[$i % count($hazardTypes)],
                'description' => 'Hazard identified during routine inspection - ' . $hazardTypes[$i % count($hazardTypes)],
                'location' => $locations[$i % count($locations)],
                'severity' => $severities[$i % count($severities)],
                'status' => $statuses[$i % count($statuses)],
                'corrective_action' => 'Corrective action implemented to mitigate the hazard',
                'action_date' => Carbon::create(2025, 1, rand(1, 31)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalRecords++;
        }

        $this->command->info("✓ Created {$totalRecords} Hazard Register Records");
    }

    private function createIncidents(): void
    {
        $totalRecords = 0;

        for ($i = 0; $i < 2; $i++) {
            $emp = $this->employees[$i];

            DB::table('incidents')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_id' => $emp['id'],
                'notice_date' => Carbon::create(2025, 1, rand(5, 25)),
                'notice_time' => rand(8, 17) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                'incident_date' => Carbon::create(2025, 1, rand(1, 25)),
                'incident_time' => rand(8, 17) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                'location' => 'Production Floor - Section ' . chr(65 + $i),
                'cause' => $i === 0 ? 'Machinery malfunction' : 'Wet floor',
                'injury_type' => $i === 0 ? 'Cut injury' : 'Slip and fall',
                'activity' => 'Operating machinery',
                'first_aid_by' => 'Safety Officer',
                'witness' => 'Supervisor and Colleague',
                'remarks' => 'Minor injury, first aid provided',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalRecords++;
        }

        $this->command->info("✓ Created {$totalRecords} Incident Records");
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('  JANUARY 2025 COMPREHENSIVE DEMO DATA SEEDING COMPLETE');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('TENANT INFORMATION:');
        $this->command->info('  Company: Demo Compliance Industries Pvt Ltd');
        $this->command->info('  Tenant ID: ' . $this->tenantId);
        $this->command->info('  Branch: Solar Panel Manufacturing Unit');
        $this->command->info('  Branch ID: ' . $this->branchId);
        $this->command->info('  Location: No.53 Nungambakkam High Road, Chennai – 600034');
        $this->command->info('');
        $this->command->info('RECORDS CREATED FOR JANUARY 2025:');
        $this->command->info('  Employees: ' . count($this->employees));
        $this->command->info('  Attendance Records: ' . (count($this->employees) * 22));
        $this->command->info('  Payroll Entries: ' . count($this->employees));
        $this->command->info('  Bonus Records: ' . count($this->employees));
        $this->command->info('  Contractors: 1');
        $this->command->info('  Contract Labour Deployments: 10');
        $this->command->info('  Hazard Register Records: 8');
        $this->command->info('  Incident Records: 2');
        $this->command->info('');
        $this->command->info('✓ All compliance forms can now be generated with realistic January 2025 data');
        $this->command->info('═══════════════════════════════════════════════════════════════');
    }
}
