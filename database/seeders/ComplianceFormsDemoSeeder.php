<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplianceFormsDemoSeeder extends Seeder
{
    private int $tenantId;
    private int $branchId;
    private array $employees = [];
    private int $contractorId;

    public function run(): void
    {
        $this->createOrGetTenant();
        $this->createOrGetBranch();
        $this->createOrGetEmployees();
        $this->createOrGetContractor();
        $this->generateAttendanceData();
        $this->generateDeductionData();
        $this->generateFineData();
        $this->generateAdvanceData();
        $this->generateContractLabourDeployment();

        $this->command->info('✓ All demo data generated successfully');
    }

    private function createOrGetTenant(): void
    {
        $tenant = DB::table('tenants')->first();
        if ($tenant) {
            $this->tenantId = $tenant->id;
            $this->command->info("Using existing tenant: {$this->tenantId}");
        } else {
            $this->tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Demo Compliance Industries',
                'address' => 'Chennai, Tamil Nadu',
                'subscription_type' => 'FULL',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Created tenant: {$this->tenantId}");
        }
    }

    private function createOrGetBranch(): void
    {
        $branch = DB::table('branches')->where('tenant_id', $this->tenantId)->first();
        if ($branch) {
            $this->branchId = $branch->id;
            $this->command->info("Using existing branch: {$this->branchId}");
        } else {
            $this->branchId = DB::table('branches')->insertGetId([
                'tenant_id' => $this->tenantId,
                'branch_name' => 'Main Manufacturing Unit',
                'address' => 'No.53 Industrial Area, Chennai',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Created branch: {$this->branchId}");
        }
    }

    private function createOrGetEmployees(): void
    {
        $existing = DB::table('workforce_employee')
            ->where('tenant_id', $this->tenantId)
            ->where('branch_id', $this->branchId)
            ->get();

        if ($existing->count() > 0) {
            $this->employees = $existing->map(fn($e) => (array)$e)->toArray();
            $this->command->info("Using existing " . count($this->employees) . " employees");
            return;
        }

        $firstNames = ['Raj', 'Kumar', 'Vijay', 'Arun', 'Suresh', 'Ramesh', 'Ganesh', 'Prakash', 'Dinesh', 'Mahesh'];
        $lastNames = ['Kumar', 'Raj', 'Prasad', 'Reddy', 'Sharma', 'Singh', 'Patel', 'Gupta', 'Verma', 'Rao'];
        $designations = ['Supervisor', 'Technician', 'Operator', 'Helper', 'Electrician'];

        for ($i = 1; $i <= 15; $i++) {
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            $fatherName = $firstNames[($i) % count($firstNames)] . ' ' . $lastNames[($i) % count($lastNames)];
            $designation = $designations[($i - 1) % count($designations)];

            $empId = DB::table('workforce_employee')->insertGetId([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_code' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $fullName,
                'father_name' => $fatherName,
                'gender' => $i % 2 === 0 ? 'F' : 'M',
                'date_of_birth' => Carbon::create(1990 + ($i % 20), rand(1, 12), rand(1, 28)),
                'permanent_address' => 'Village ' . chr(65 + ($i % 26)) . ', District, State',
                'local_address' => 'Street ' . $i . ', Chennai',
                'pf_number' => 'PF/TN/2025/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'esi_number' => 'ESI/TN/2025/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date_of_joining' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                'designation' => $designation,
                'department' => 'Production',
                'basic_salary' => 20000 + ($i * 1000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->employees[] = [
                'id' => $empId,
                'name' => $fullName,
                'basic_salary' => 20000 + ($i * 1000),
            ];
        }

        $this->command->info("Created " . count($this->employees) . " employees");
    }

    private function createOrGetContractor(): void
    {
        $contractor = DB::table('contractor_master')->where('tenant_id', $this->tenantId)->first();
        if ($contractor) {
            $this->contractorId = $contractor->id;
            $this->command->info("Using existing contractor: {$this->contractorId}");
            return;
        }

        $this->contractorId = DB::table('contractor_master')->insertGetId([
            'tenant_id' => $this->tenantId,
            'company_type' => 'Manpower',
            'company_name' => 'GIRI Manpower Services',
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

        $this->command->info("Created contractor: {$this->contractorId}");
    }

    private function generateAttendanceData(): void
    {
        $existing = DB::table('workforce_attendance')
            ->where('tenant_id', $this->tenantId)
            ->count();

        if ($existing > 0) {
            $this->command->info("Attendance data already exists");
            return;
        }

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2025, 3, 31);
        $statuses = ['present', 'absent', 'leave', 'holiday'];
        $count = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            foreach ($this->employees as $emp) {
                $status = $statuses[rand(0, 3)];

                DB::table('workforce_attendance')->insert([
                    'tenant_id' => $this->tenantId,
                    'employee_id' => $emp['id'],
                    'branch_id' => $this->branchId,
                    'attendance_date' => $date->toDateString(),
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        $this->command->info("Generated $count attendance records");
    }

    private function generateDeductionData(): void
    {
        $existing = DB::table('workforce_deductions')
            ->where('tenant_id', $this->tenantId)
            ->count();

        if ($existing > 0) {
            $this->command->info("Deduction data already exists");
            return;
        }

        $count = 0;
        for ($i = 0; $i < 5; $i++) {
            $emp = $this->employees[$i];

            DB::table('workforce_deductions')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_id' => $emp['id'],
                'deduction_date' => Carbon::create(2025, 1, rand(1, 28)),
                'particulars' => 'Damage to equipment',
                'showed_cause' => true,
                'witness_name' => 'Supervisor Name',
                'amount' => rand(500, 2000),
                'num_instalments' => 2,
                'first_month' => 'January 2025',
                'last_month' => 'February 2025',
                'remarks' => 'Deduction approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->command->info("Generated $count deduction records");
    }

    private function generateFineData(): void
    {
        $existing = DB::table('workforce_fines')
            ->where('tenant_id', $this->tenantId)
            ->count();

        if ($existing > 0) {
            $this->command->info("Fine data already exists");
            return;
        }

        $reasons = ['Absenteeism', 'Insubordination', 'Safety violation', 'Quality defect', 'Misconduct'];
        $count = 0;

        for ($i = 0; $i < 8; $i++) {
            $emp = $this->employees[$i];

            DB::table('workforce_fines')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_id' => $emp['id'],
                'fine_date' => Carbon::create(2025, 2, rand(1, 28)),
                'reason' => $reasons[$i % count($reasons)],
                'amount' => rand(200, 1000),
                'remarks' => 'Fine imposed as per company policy',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->command->info("Generated $count fine records");
    }

    private function generateAdvanceData(): void
    {
        $existing = DB::table('workforce_advances')
            ->where('tenant_id', $this->tenantId)
            ->count();

        if ($existing > 0) {
            $this->command->info("Advance data already exists");
            return;
        }

        $count = 0;
        for ($i = 0; $i < 6; $i++) {
            $emp = $this->employees[$i];

            DB::table('workforce_advances')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_id' => $emp['id'],
                'advance_date' => Carbon::create(2025, 1, rand(1, 28)),
                'amount' => rand(5000, 15000),
                'num_instalments' => 3,
                'first_month' => 'February 2025',
                'last_month' => 'April 2025',
                'remarks' => 'Salary advance approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->command->info("Generated $count advance records");
    }

    private function generateContractLabourDeployment(): void
    {
        $existing = DB::table('contract_labour_deployment')
            ->where('tenant_id', $this->tenantId)
            ->count();

        if ($existing > 0) {
            $this->command->info("Contract labour deployment data already exists");
            return;
        }

        $count = 0;
        for ($i = 0; $i < 10; $i++) {
            $emp = $this->employees[$i];

            DB::table('contract_labour_deployment')->insert([
                'tenant_id' => $this->tenantId,
                'contractor_id' => $this->contractorId,
                'employee_id' => $emp['id'],
                'branch_id' => $this->branchId,
                'wage_rate' => $emp['basic_salary'],
                'deployment_start' => Carbon::create(2025, 1, 1),
                'deployment_end' => Carbon::create(2025, 12, 31),
                'work_order_number' => 'WO/2025/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'work_order_date' => Carbon::create(2024, 12, 15),
                'nature_of_work' => 'Manufacturing',
                'work_location' => 'Main Unit',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->command->info("Generated $count contract labour deployments");
    }
}
