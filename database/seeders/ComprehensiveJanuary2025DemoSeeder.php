<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ContractorMaster;
use App\Models\WorkforceEmployee;
use App\Models\ContractLabourDeployment;
use App\Models\WorkforcePayrollCycle;
use App\Models\WorkforcePayrollEntry;
use App\Models\WorkforceAttendance;
use App\Models\IncidentDocument;
use App\Models\EmployeeLeave;
use App\Models\HazardRegister;
use App\Models\BonusRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComprehensiveJanuary2025DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Step 1: Get existing tenant and branch
            $tenant = Tenant::first();
            if (!$tenant) {
                $this->command->error('No tenant found. Please create a tenant first.');
                return;
            }

            $branch = $tenant->branches()->first();
            if (!$branch) {
                $this->command->error('No branch found for tenant. Please create a branch first.');
                return;
            }

            $tenantId = $tenant->id;
            $branchId = $branch->id;

            $this->command->info("Using Tenant ID: {$tenantId}, Branch ID: {$branchId}");

            // Step 2: Create contractors
            $this->createContractors($tenantId, $branchId);

            // Step 3: Create employees
            $employees = $this->createEmployees($tenantId, $branchId);

            // Step 4: Create contract labour deployments
            $this->createContractLabourDeployments($tenantId, $branchId, $employees);

            // Step 5: Create payroll cycle
            $payrollCycle = $this->createPayrollCycle($tenantId, $branchId);

            // Step 6: Create payroll entries
            $this->createPayrollEntries($tenantId, $branchId, $payrollCycle, $employees);

            // Step 7: Create attendance records
            $this->createAttendanceRecords($tenantId, $branchId, $employees);

            // Step 8: Create accident records
            $this->createAccidentRecords($tenantId, $branchId, $employees);

            // Step 9: Create advances
            $this->createAdvances($tenantId, $branchId, $employees);

            // Step 10: Create fines
            $this->createFines($tenantId, $branchId, $employees);

            // Step 11: Create bonus records
            $this->createBonusRecords($tenantId, $branchId, $employees);

            // Step 12: Create leave records
            $this->createLeaveRecords($tenantId, $branchId, $employees);

            // Step 13: Create hazard register
            $this->createHazardRegister($tenantId, $branchId);

            $this->command->info('✅ Demo dataset created successfully for January 2025!');
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function createContractors($tenantId, $branchId): void
    {
        $contractors = [
            [
                'company_name' => 'Alpha Industrial Services',
                'company_address' => '123 Industrial Park, Chennai',
                'contact_person' => 'Rajesh Kumar',
                'contact_number' => '9876543210',
                'email' => 'rajesh@alpha.com',
                'pan_number' => 'AAAPA1234A',
                'gst_number' => '33AABPA1234A1Z0',
                'license_number' => 'LIC001',
                'valid_from' => Carbon::create(2024, 1, 1),
                'valid_to' => Carbon::create(2026, 12, 31),
                'max_worker_limit' => 100,
            ],
            [
                'company_name' => 'Metro Labour Contractors',
                'company_address' => '456 Business District, Chennai',
                'contact_person' => 'Priya Singh',
                'contact_number' => '9876543211',
                'email' => 'priya@metro.com',
                'pan_number' => 'AAAPB1234B',
                'gst_number' => '33AABPB1234B1Z0',
                'license_number' => 'LIC002',
                'valid_from' => Carbon::create(2024, 1, 1),
                'valid_to' => Carbon::create(2026, 12, 31),
                'max_worker_limit' => 150,
            ],
            [
                'company_name' => 'Prime Workforce Solutions',
                'company_address' => '789 Tech Park, Chennai',
                'contact_person' => 'Vikram Patel',
                'contact_number' => '9876543212',
                'email' => 'vikram@prime.com',
                'pan_number' => 'AAAPC1234C',
                'gst_number' => '33AABPC1234C1Z0',
                'license_number' => 'LIC003',
                'valid_from' => Carbon::create(2024, 1, 1),
                'valid_to' => Carbon::create(2026, 12, 31),
                'max_worker_limit' => 120,
            ],
        ];

        foreach ($contractors as $contractor) {
            ContractorMaster::firstOrCreate(
                ['tenant_id' => $tenantId, 'company_name' => $contractor['company_name']],
                array_merge($contractor, ['tenant_id' => $tenantId, 'status' => 'active'])
            );
        }

        $this->command->info('✓ Created 3 contractors');
    }

    private function createEmployees($tenantId, $branchId): array
    {
        $designations = ['Supervisor', 'Technician', 'Machine Operator', 'Helper', 'Electrician', 'Safety Officer'];
        $genders = ['M', 'F'];
        $employees = [];

        for ($i = 1; $i <= 25; $i++) {
            $code = 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $designation = $designations[($i - 1) % count($designations)];
            $gender = $genders[$i % 2];

            $employee = WorkforceEmployee::firstOrCreate(
                ['tenant_id' => $tenantId, 'employee_code' => $code],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_code' => $code,
                    'name' => "Employee {$i}",
                    'gender' => $gender,
                    'designation' => $designation,
                    'father_name' => "Father of Employee {$i}",
                    'date_of_joining' => Carbon::create(2024, 1, 1),
                    'basic_salary' => 18000 + ($i * 500),
                    'status' => 'active',
                ]
            );

            $employees[] = $employee;
        }

        $this->command->info('✓ Created 25 employees');
        return $employees;
    }

    private function createContractLabourDeployments($tenantId, $branchId, $employees): void
    {
        $contractors = ContractorMaster::where('tenant_id', $tenantId)->get();

        foreach ($employees as $index => $employee) {
            $contractor = $contractors[$index % $contractors->count()];

            ContractLabourDeployment::firstOrCreate(
                ['tenant_id' => $tenantId, 'employee_id' => $employee->id],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $employee->id,
                    'contractor_id' => $contractor->id,
                    'deployment_start' => Carbon::create(2025, 1, 1),
                    'wage_rate' => $employee->basic_salary ?? 20000,
                    'work_description' => 'Solar Panel Manufacturing Unit',
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('✓ Created contract labour deployments');
    }

    private function createPayrollCycle($tenantId, $branchId)
    {
        $cycle = WorkforcePayrollCycle::firstOrCreate(
            ['tenant_id' => $tenantId, 'period_from' => '2025-01-01', 'period_to' => '2025-01-31'],
            [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'cycle_name' => 'January 2025',
                'period_from' => Carbon::create(2025, 1, 1),
                'period_to' => Carbon::create(2025, 1, 31),
                'status' => 'processed',
            ]
        );

        $this->command->info('✓ Created payroll cycle');
        return $cycle;
    }

    private function createPayrollEntries($tenantId, $branchId, $payrollCycle, $employees): void
    {
        foreach ($employees as $employee) {
            $basicSalary = $employee->basic_salary ?? 20000;
            $da = $basicSalary * 0.15;
            $hra = $basicSalary * 0.10;
            $grossSalary = $basicSalary + $da + $hra;
            $pf = $basicSalary * 0.12;
            $esi = $basicSalary * 0.0475;
            $deductions = $pf + $esi;

            WorkforcePayrollEntry::firstOrCreate(
                ['tenant_id' => $tenantId, 'payroll_cycle_id' => $payrollCycle->id, 'employee_id' => $employee->id],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'payroll_cycle_id' => $payrollCycle->id,
                    'employee_id' => $employee->id,
                    'total_days_worked' => 26,
                    'paid_leave_days' => 0,
                    'unpaid_leave_days' => 0,
                    'overtime_hours' => rand(0, 20),
                    'basic_earned' => $basicSalary,
                    'da_earned' => $da,
                    'hra_earned' => $hra,
                    'other_allowances' => 0,
                    'overtime_wages' => rand(0, 5000),
                    'gross_salary' => $grossSalary,
                    'pf_employee' => $pf,
                    'esi_employee' => $esi,
                    'professional_tax' => 0,
                    'fines' => 0,
                    'advances' => 0,
                    'other_deductions' => 0,
                    'total_deductions' => $deductions,
                    'net_salary' => $grossSalary - $deductions,
                    'payment_date' => Carbon::create(2025, 1, 31),
                    'payment_mode' => 'Bank Transfer',
                ]
            );
        }

        $this->command->info('✓ Created payroll entries for all employees');
    }

    private function createAttendanceRecords($tenantId, $branchId, $employees): void
    {
        $statuses = ['present', 'absent', 'holiday', 'leave'];
        $holidays = [26, 27];

        for ($day = 1; $day <= 31; $day++) {
            $date = Carbon::create(2025, 1, $day);

            foreach ($employees as $employee) {
                if (in_array($day, $holidays)) {
                    $status = 'holiday';
                } else {
                    $status = $statuses[array_rand($statuses)];
                }

                WorkforceAttendance::firstOrCreate(
                    ['tenant_id' => $tenantId, 'employee_id' => $employee->id, 'attendance_date' => $date],
                    [
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'employee_id' => $employee->id,
                        'attendance_date' => $date,
                        'status' => $status,
                    ]
                );
            }
        }

        $this->command->info('✓ Created attendance records for January 2025');
    }

    private function createAccidentRecords($tenantId, $branchId, $employees): void
    {
        $incidents = [
            [
                'employee_id' => $employees[0]->id,
                'incident_type' => 'accident',
                'incident_date' => Carbon::create(2025, 1, 10),
                'location' => 'Manufacturing Floor',
                'description' => 'Minor cut on left hand during machine operation',
            ],
            [
                'employee_id' => $employees[1]->id,
                'incident_type' => 'accident',
                'incident_date' => Carbon::create(2025, 1, 20),
                'location' => 'Maintenance Area',
                'description' => 'Minor bruise during equipment maintenance',
            ],
        ];

        $user = User::where('tenant_id', $tenantId)->first();
        $userId = $user ? $user->id : 1;

        foreach ($incidents as $incident) {
            IncidentDocument::firstOrCreate(
                ['tenant_id' => $tenantId, 'employee_id' => $incident['employee_id'], 'incident_date' => $incident['incident_date']],
                array_merge($incident, [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'authority_name' => 'Factory Inspector',
                    'reference_number' => 'INC-' . date('YmdHis'),
                    'document_path' => 'incidents/demo_' . date('YmdHis') . '.pdf',
                    'uploaded_by' => $userId,
                    'uploaded_at' => now(),
                ])
            );
        }

        $this->command->info('✓ Created accident records');
    }

    private function createAdvances($tenantId, $branchId, $employees): void
    {
        $advances = [
            ['employee_id' => $employees[0]->id, 'amount' => 5000, 'date' => Carbon::create(2025, 1, 5)],
            ['employee_id' => $employees[2]->id, 'amount' => 3000, 'date' => Carbon::create(2025, 1, 8)],
            ['employee_id' => $employees[4]->id, 'amount' => 7500, 'date' => Carbon::create(2025, 1, 12)],
        ];

        foreach ($advances as $advance) {
            DB::table('workforce_advances')->updateOrInsert(
                ['tenant_id' => $tenantId, 'employee_id' => $advance['employee_id'], 'advance_date' => $advance['date']],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $advance['employee_id'],
                    'advance_date' => $advance['date'],
                    'amount' => $advance['amount'],
                    'num_instalments' => 3,
                    'first_month' => 'February',
                    'last_month' => 'April',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✓ Created advance records');
    }

    private function createFines($tenantId, $branchId, $employees): void
    {
        $fines = [
            ['employee_id' => $employees[1]->id, 'reason' => 'Late arrival', 'amount' => 500, 'date' => Carbon::create(2025, 1, 7)],
            ['employee_id' => $employees[3]->id, 'reason' => 'Safety violation', 'amount' => 1000, 'date' => Carbon::create(2025, 1, 15)],
            ['employee_id' => $employees[5]->id, 'reason' => 'Unauthorized absence', 'amount' => 750, 'date' => Carbon::create(2025, 1, 22)],
        ];

        foreach ($fines as $fine) {
            DB::table('workforce_fines')->updateOrInsert(
                ['tenant_id' => $tenantId, 'employee_id' => $fine['employee_id'], 'fine_date' => $fine['date']],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $fine['employee_id'],
                    'fine_date' => $fine['date'],
                    'reason' => $fine['reason'],
                    'amount' => $fine['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✓ Created fine records');
    }

    private function createBonusRecords($tenantId, $branchId, $employees): void
    {
        foreach ($employees as $employee) {
            BonusRecord::firstOrCreate(
                ['tenant_id' => $tenantId, 'employee_id' => $employee->id, 'financial_year' => '2024-25'],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $employee->id,
                    'financial_year' => '2024-25',
                    'bonus_percentage' => 8.33,
                    'bonus_amount' => ($employee->basic_salary ?? 20000) * 0.0833,
                    'payment_date' => Carbon::create(2025, 1, 31),
                    'status' => 'paid',
                ]
            );
        }

        $this->command->info('✓ Created bonus records');
    }

    private function createLeaveRecords($tenantId, $branchId, $employees): void
    {
        $leaveTypes = ['Casual Leave', 'Medical Leave', 'Earned Leave'];

        $leaves = [
            ['employee_id' => $employees[0]->id, 'leave_type' => 'Medical Leave', 'from' => Carbon::create(2025, 1, 13), 'to' => Carbon::create(2025, 1, 14)],
            ['employee_id' => $employees[2]->id, 'leave_type' => 'Casual Leave', 'from' => Carbon::create(2025, 1, 20), 'to' => Carbon::create(2025, 1, 21)],
            ['employee_id' => $employees[4]->id, 'leave_type' => 'Earned Leave', 'from' => Carbon::create(2025, 1, 27), 'to' => Carbon::create(2025, 1, 28)],
        ];

        foreach ($leaves as $leave) {
            EmployeeLeave::firstOrCreate(
                ['tenant_id' => $tenantId, 'employee_id' => $leave['employee_id'], 'leave_from' => $leave['from']],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $leave['employee_id'],
                    'leave_from' => $leave['from'],
                    'leave_to' => $leave['to'],
                    'leave_type' => $leave['leave_type'],
                    'days' => $leave['to']->diffInDays($leave['from']) + 1,
                    'status' => 'approved',
                ]
            );
        }

        $this->command->info('✓ Created leave records');
    }

    private function createHazardRegister($tenantId, $branchId): void
    {
        $hazards = [
            [
                'hazard_type' => 'Electrical hazard',
                'description' => 'Exposed electrical wiring in manufacturing area',
                'location' => 'Production Floor - Section A',
                'severity' => 'High',
                'hazard_date' => Carbon::create(2025, 1, 5),
                'corrective_action' => 'Wiring insulated and covered',
                'action_date' => Carbon::create(2025, 1, 6),
            ],
            [
                'hazard_type' => 'Chemical spill',
                'description' => 'Minor chemical spill in storage area',
                'location' => 'Chemical Storage - Section B',
                'severity' => 'Medium',
                'hazard_date' => Carbon::create(2025, 1, 12),
                'corrective_action' => 'Area cleaned and ventilated',
                'action_date' => Carbon::create(2025, 1, 12),
            ],
            [
                'hazard_type' => 'Machinery guard missing',
                'description' => 'Safety guard missing on lathe machine',
                'location' => 'Machine Shop - Section C',
                'severity' => 'High',
                'hazard_date' => Carbon::create(2025, 1, 18),
                'corrective_action' => 'Guard installed and tested',
                'action_date' => Carbon::create(2025, 1, 19),
            ],
        ];

        foreach ($hazards as $hazard) {
            HazardRegister::firstOrCreate(
                ['tenant_id' => $tenantId, 'hazard_date' => $hazard['hazard_date'], 'hazard_type' => $hazard['hazard_type']],
                array_merge($hazard, [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'status' => 'resolved',
                ])
            );
        }

        $this->command->info('✓ Created hazard register records');
    }
}
