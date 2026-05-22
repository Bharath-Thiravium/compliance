<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplianceFullCoverageSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 4;
        $branchId = 4;
        $periodMonth = 1; // January 2026
        $periodYear = 2026;

        $this->command->info('Clearing existing data for tenant 4...');
        
        // Clear existing data for tenant 4
        DB::table('workforce_payroll_entry')->where('tenant_id', $tenantId)->delete();
        DB::table('workforce_attendance')->where('tenant_id', $tenantId)->delete();
        DB::table('bonus_records')->where('tenant_id', $tenantId)->delete();
        DB::table('incident_documents')->where('tenant_id', $tenantId)->delete();
        DB::table('inspection_documents')->where('tenant_id', $tenantId)->delete();
        DB::table('contract_labour_deployment')->where('tenant_id', $tenantId)->delete();
        DB::table('clra_returns')->where('tenant_id', $tenantId)->delete();
        DB::table('contractor_master')->where('tenant_id', $tenantId)->delete();
        DB::table('workforce_payroll_cycle')->where('tenant_id', $tenantId)->delete();
        DB::table('workforce_employee')->where('tenant_id', $tenantId)->delete();

        $this->command->info('Seeding 30 employees with attendance data...');

        // 1. Seed 30 Workforce Employees
        $employees = $this->seedEmployees($tenantId, $branchId);
        $this->command->info('✓ 30 employees created');

        // 2. Seed Attendance (30 employees × 31 days)
        $this->seedAttendance($tenantId, $employees, $periodMonth, $periodYear);
        $this->command->info('✓ Attendance records created');

        // 3. Seed Bonus Records
        $this->seedBonusRecords($tenantId, $employees);
        $this->command->info('✓ Bonus records created');

        // 4. Seed Incident Documents
        $this->seedIncidentDocuments($tenantId, $employees);
        $this->command->info('✓ Incident documents created');

        // 5. Seed Inspection Documents
        $this->seedInspectionDocuments($tenantId, $branchId);
        $this->command->info('✓ Inspection documents created');

        // 6. Seed Contractors
        $contractors = $this->seedContractors($tenantId);
        $this->command->info('✓ 5 contractors created');

        // 7. Seed Contract Labour Deployments
        $this->seedContractLabourDeployments($tenantId, $branchId, $contractors, $employees);
        $this->command->info('✓ Contract labour deployments created');

        // 8. Seed CLRA Returns
        $this->seedClraReturns($tenantId);
        $this->command->info('✓ CLRA returns created');

        $this->command->info('');
        $this->command->info('✅ Seeding complete!');
        $this->command->info('Next: php artisan compliance:process-payroll 4 4 1 2026');
        $this->command->info('Then: php artisan compliance:test-generation --all');
    }

    private function seedEmployees(int $tenantId, int $branchId): array
    {
        $employees = [];
        $designations = ['Manager', 'Supervisor', 'Operator', 'Technician', 'Helper', 'Clerk', 'Engineer'];
        $departments = ['Production', 'Maintenance', 'Quality', 'Admin', 'Stores'];
        $names = [
            'Rajesh Kumar', 'Priya Sharma', 'Amit Patel', 'Sunita Singh', 'Vijay Reddy',
            'Anjali Gupta', 'Suresh Rao', 'Kavita Nair', 'Manoj Verma', 'Deepa Iyer',
            'Ravi Shankar', 'Meena Desai', 'Anil Joshi', 'Pooja Mehta', 'Sanjay Kumar',
            'Rekha Pillai', 'Ramesh Babu', 'Lakshmi Menon', 'Prakash Reddy', 'Swati Kulkarni',
            'Dinesh Yadav', 'Nisha Agarwal', 'Kiran Bhat', 'Madhavi Rao', 'Venkat Raman',
            'Shobha Shetty', 'Ganesh Naik', 'Usha Kamath', 'Mohan Das', 'Radha Krishna'
        ];

        foreach ($names as $index => $name) {
            $empCode = 'EMP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $pfNumber = 'PF' . str_pad($index + 1, 8, '0', STR_PAD_LEFT);
            $esiNumber = 'ESI' . str_pad($index + 1, 10, '0', STR_PAD_LEFT);

            $employeeId = DB::table('workforce_employee')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_code' => $empCode,
                'name' => $name,
                'designation' => $designations[array_rand($designations)],
                'department' => $departments[array_rand($departments)],
                'pf_number' => $pfNumber,
                'esi_number' => $esiNumber,
                'date_of_joining' => Carbon::create(2025, rand(1, 12), rand(1, 28)),
                'basic_salary' => rand(15000, 50000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employees[] = $employeeId;
        }

        return $employees;
    }


    {
        $daysInMonth = Carbon::create($periodYear, $periodMonth, 1)->daysInMonth;

        foreach ($employees as $employeeId) {
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $status = (rand(1, 100) > 10) ? 'present' : 'absent'; // 90% attendance

                DB::table('workforce_attendance')->insert([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                    'attendance_date' => Carbon::create($periodYear, $periodMonth, $day),
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedBonusRecords(int $tenantId, array $employees): void
    {
        foreach (array_slice($employees, 0, 15) as $employeeId) {
            DB::table('bonus_records')->insert([
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'bonus_amount' => rand(5000, 20000),
                'bonus_percentage' => 8.33,
                'payment_date' => Carbon::create(2025, 12, 25),
                'financial_year' => '2025-2026',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedIncidentDocuments(int $tenantId, array $employees): void
    {
        $incidentTypes = ['accident', 'serious', 'dangerous'];
        $locations = ['Production Floor', 'Warehouse', 'Loading Bay'];
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id') ?? 1;

        // Use first 3 employees for incidents
        for ($i = 0; $i < 3; $i++) {
            DB::table('incident_documents')->insert([
                'tenant_id' => $tenantId,
                'employee_id' => $employees[$i],
                'incident_date' => Carbon::create(2026, 1, rand(5, 25)),
                'incident_type' => $incidentTypes[$i % 3],
                'location' => $locations[$i % 3],
                'description' => 'Incident occurred during routine operations. First aid provided.',
                'document_path' => 'incidents/incident_' . ($i + 1) . '.pdf',
                'uploaded_by' => $userId,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedInspectionDocuments(int $tenantId, int $branchId): void
    {
        $authorities = ['EPF Inspector', 'Factory Inspector'];
        $types = ['epf', 'factory'];
        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id') ?? 1;

        for ($i = 0; $i < 2; $i++) {
            DB::table('inspection_documents')->insert([
                'tenant_id' => $tenantId,
                'inspection_date' => Carbon::create(2026, 1, rand(10, 20)),
                'inspection_type' => $types[$i],
                'inspecting_authority' => $authorities[$i],
                'reference_number' => 'INS/2026/' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'remarks' => 'Routine inspection completed. All records found in order.',
                'document_path' => 'inspections/inspection_' . ($i + 1) . '.pdf',
                'uploaded_by' => $userId,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedContractors(int $tenantId): array
    {
        $contractors = [];
        $contractorNames = [
            'ABC Manpower Services',
            'XYZ Labour Contractors',
            'Global Workforce Solutions',
            'Prime Staffing Services',
            'Elite Labour Providers'
        ];

        foreach ($contractorNames as $index => $name) {
            $contractorId = DB::table('contractor_master')->insertGetId([
                'tenant_id' => $tenantId,
                'company_name' => $name,
                'license_number' => 'CLRA/' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'valid_from' => Carbon::create(2025, 1, 1),
                'valid_to' => Carbon::create(2026, 12, 31),
                'contact_person' => 'Manager ' . ($index + 1),
                'contact_number' => '98765432' . ($index + 10),
                'max_worker_limit' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $contractors[] = $contractorId;
        }

        return $contractors;
    }

    private function seedContractLabourDeployments(int $tenantId, int $branchId, array $contractors, array $employees): void
    {
        // Use first 15 employees as contract labour
        $contractEmployees = array_slice($employees, 0, 15);

        foreach ($contractEmployees as $index => $employeeId) {
            $contractorId = $contractors[$index % count($contractors)];

            DB::table('contract_labour_deployment')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'contractor_id' => $contractorId,
                'employee_id' => $employeeId,
                'deployment_start' => Carbon::create(2026, 1, 1),
                'deployment_end' => Carbon::create(2026, 1, 31),
                'wage_rate' => rand(400, 800),
                'work_order_number' => 'WO/2026/' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedClraReturns(int $tenantId): void
    {
        DB::table('clra_returns')->insert([
            'tenant_id' => $tenantId,
            'period_from' => Carbon::create(2025, 7, 1),
            'period_to' => Carbon::create(2025, 12, 31),
            'return_type' => 'half_yearly',
            'total_workers' => 15,
            'total_wages' => 450000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('clra_returns')->insert([
            'tenant_id' => $tenantId,
            'period_from' => Carbon::create(2025, 1, 1),
            'period_to' => Carbon::create(2025, 12, 31),
            'return_type' => 'annual',
            'total_workers' => 15,
            'total_wages' => 900000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
