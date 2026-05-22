<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComplianceFullDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing demo data
        DB::table('workforce_payroll_entry')->delete();
        DB::table('workforce_payroll_cycle')->delete();
        DB::table('contract_labour_deployment')->delete();
        DB::table('contractor_master')->delete();
        DB::table('incident_documents')->delete();
        DB::table('inspection_documents')->delete();
        DB::table('workforce_employee')->delete();
        DB::table('users')->whereIn('email', ['admin@abc.com', 'minimal@demo.com'])->delete();
        DB::table('branches')->delete();
        DB::table('tenants')->where('name', 'ABC Manufacturing Pvt Ltd')->delete();
        
        DB::beginTransaction();
        
        try {
            // TENANT
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'ABC Manufacturing Pvt Ltd',
                'subscription_type' => 'FULL',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // BRANCH
            $branchId = DB::table('branches')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_name' => 'Main Factory Unit',
                'address' => 'Plot No. 45, Industrial Area, Phase-II, Bangalore - 560058',
                'factory_license_number' => 'KAR/BLR/FAC/2024/001234',
                'pf_code' => 'KARBG12345000',
                'esi_code' => 'ESI-KAR-BLR-001234',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // USERS
            $userId1 = DB::table('users')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => 'Admin User',
                'email' => 'admin@abc.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('users')->insert([
                'tenant_id' => $tenantId,
                'name' => 'Minimal User',
                'email' => 'minimal@demo.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // EMPLOYEES
            $employees = [
                ['EMP001', 'Rajesh Kumar', 'Production Supervisor', 35000],
                ['EMP002', 'Priya Sharma', 'Quality Inspector', 28000],
                ['EMP003', 'Amit Patel', 'Machine Operator', 22000],
                ['EMP004', 'Sunita Reddy', 'Assembly Worker', 18000],
                ['EMP005', 'Vikram Singh', 'Maintenance Engineer', 32000],
                ['EMP006', 'Lakshmi Iyer', 'Store Keeper', 20000],
                ['EMP007', 'Mohammed Ali', 'Electrician', 25000],
                ['EMP008', 'Anjali Desai', 'HR Executive', 30000],
                ['EMP009', 'Ravi Verma', 'Security Officer', 16000],
                ['EMP010', 'Kavita Nair', 'Admin Assistant', 19000],
            ];

            $employeeIds = [];
            foreach ($employees as $emp) {
                $employeeIds[] = DB::table('workforce_employee')->insertGetId([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_code' => $emp[0],
                    'name' => $emp[1],
                    'designation' => $emp[2],
                    'esi_number' => 'ESI' . rand(1000000000, 9999999999),
                    'pf_number' => 'PF' . rand(1000000000, 9999999999),
                    'basic_salary' => $emp[3],
                    'date_of_joining' => Carbon::create(2025, 1, 1)->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // PAYROLL CYCLE
            $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                'tenant_id' => $tenantId,
                'cycle_name' => 'January 2026',
                'period_from' => Carbon::create(2026, 1, 1)->format('Y-m-d'),
                'period_to' => Carbon::create(2026, 1, 31)->format('Y-m-d'),
                'status' => 'processed',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // PAYROLL ENTRIES
            foreach ($employeeIds as $index => $empId) {
                $basic = $employees[$index][3];
                $da = $basic * 0.40;
                $hra = $basic * 0.30;
                $overtime = rand(0, 5) * 200;
                $gross = $basic + $da + $hra + $overtime;
                $pf = $basic * 0.12;
                $esi = $gross * 0.0075;
                $deductions = $pf + $esi;
                $net = $gross - $deductions;

                DB::table('workforce_payroll_entry')->insert([
                    'tenant_id' => $tenantId,
                    'employee_id' => $empId,
                    'payroll_cycle_id' => $cycleId,
                    'basic_earned' => $basic,
                    'da_earned' => $da,
                    'hra_earned' => $hra,
                    'overtime_hours' => rand(0, 20),
                    'overtime_wages' => $overtime,
                    'gross_salary' => $gross,
                    'pf_employee' => $pf,
                    'esi_employee' => $esi,
                    'total_deductions' => $deductions,
                    'net_salary' => $net,
                    'total_days_worked' => 26,
                    'advances' => 0,
                    'fines' => 0,
                    'created_at' => Carbon::create(2026, 1, 15),
                    'updated_at' => Carbon::create(2026, 1, 15),
                ]);
            }

            // CONTRACTORS
            $contractor1 = DB::table('contractor_master')->insertGetId([
                'tenant_id' => $tenantId,
                'company_name' => 'XYZ Labour Solutions Pvt Ltd',
                'license_number' => 'CLRA/KAR/2024/5678',
                'contact_person' => 'Suresh Kumar',
                'valid_from' => Carbon::create(2024, 1, 1)->format('Y-m-d'),
                'valid_to' => Carbon::create(2027, 12, 31)->format('Y-m-d'),
                'max_worker_limit' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // CONTRACT WORKERS (using existing employees as contract workers)
            for ($i = 0; $i < 5; $i++) {
                DB::table('contract_labour_deployment')->insert([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'contractor_id' => $contractor1,
                    'employee_id' => $employeeIds[$i],
                    'deployment_start' => Carbon::create(2026, 1, 1)->format('Y-m-d'),
                    'deployment_end' => null,
                    'wage_rate' => 450 + ($i * 10),
                    'work_order_number' => 'WO/2026/' . rand(1000, 9999),
                    'created_at' => Carbon::create(2026, 1, 1),
                    'updated_at' => Carbon::create(2026, 1, 1),
                ]);
            }

            // INCIDENTS
            DB::table('incident_documents')->insert([
                'tenant_id' => $tenantId,
                'employee_id' => $employeeIds[2],
                'incident_date' => Carbon::create(2026, 1, 15)->format('Y-m-d H:i:s'),
                'incident_type' => 'accident',
                'location' => 'Production Floor - Section A',
                'description' => 'Minor cut on hand while operating machinery',
                'document_path' => 'incidents/demo_incident.pdf',
                'uploaded_by' => $userId1,
                'uploaded_at' => Carbon::create(2026, 1, 15),
                'created_at' => Carbon::create(2026, 1, 15),
                'updated_at' => Carbon::create(2026, 1, 15),
            ]);

            // INSPECTIONS
            DB::table('inspection_documents')->insert([
                'tenant_id' => $tenantId,
                'inspection_type' => 'epf',
                'inspection_date' => Carbon::create(2026, 1, 20)->format('Y-m-d'),
                'inspecting_authority' => 'Regional PF Commissioner - Bangalore',
                'reference_number' => 'RPFC/BLR/INSP/2026/0123',
                'remarks' => 'Routine inspection conducted. All records found in order.',
                'document_path' => 'inspections/demo_inspection.pdf',
                'uploaded_by' => $userId1,
                'uploaded_at' => Carbon::create(2026, 1, 20),
                'created_at' => Carbon::create(2026, 1, 20),
                'updated_at' => Carbon::create(2026, 1, 20),
            ]);

            DB::commit();
            
            $this->command->info('✅ Demo data seeded successfully!');
            $this->command->info('   Tenant: ABC Manufacturing Pvt Ltd (ID: ' . $tenantId . ')');
            $this->command->info('   Branch: Main Factory Unit (ID: ' . $branchId . ')');
            $this->command->info('   Employees: 10');
            $this->command->info('   Contract Workers: 5');
            $this->command->info('   Payroll Period: January 2026');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
