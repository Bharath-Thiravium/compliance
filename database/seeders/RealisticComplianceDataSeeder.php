<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealisticComplianceDataSeeder extends Seeder
{
    private int $tenantId;
    private int $branchId;
    private array $employees = [];
    private string $month = '2026-01';
    
    public function run(): void
    {
        // Get FULL tenant and branch
        $this->tenantId = DB::table('tenants')->where('subscription_type', 'FULL')->value('id');
        $this->branchId = DB::table('branches')->where('tenant_id', $this->tenantId)->value('id');
        
        if (!$this->tenantId || !$this->branchId) {
            $this->command->error('FULL tenant or branch not found. Run SystemStabilizationSeeder first.');
            return;
        }
        
        $this->command->info('Seeding data for Tenant ID: ' . $this->tenantId . ', Branch ID: ' . $this->branchId);
        
        // Clear existing data for this tenant
        DB::table('workforce_attendance')->where('tenant_id', $this->tenantId)->delete();
        DB::table('bonus_records')->where('tenant_id', $this->tenantId)->delete();
        DB::table('incident_documents')->where('tenant_id', $this->tenantId)->delete();
        DB::table('contract_labour_deployment')->where('tenant_id', $this->tenantId)->delete();
        DB::table('clra_returns')->where('tenant_id', $this->tenantId)->delete();
        DB::table('contractors')->where('tenant_id', $this->tenantId)->delete();
        DB::table('workforce_employee')->where('tenant_id', $this->tenantId)->delete();
        
        $this->seedEmployees();
        $this->seedContractors();
        $this->seedAttendance();
        $this->seedBonusRecords();
        $this->seedAccidents();
        $this->seedCLRAReturns();
        
        $this->printSummary();
    }
    
    private function seedEmployees(): void
    {
        $this->command->info('PHASE 1: Creating 35 employees...');
        
        $roles = [
            ['title' => 'Helper', 'count' => 8, 'salary_min' => 14000, 'salary_max' => 18000, 'dept' => 'Production'],
            ['title' => 'Operator', 'count' => 6, 'salary_min' => 18000, 'salary_max' => 25000, 'dept' => 'Production'],
            ['title' => 'Technician', 'count' => 6, 'salary_min' => 25000, 'salary_max' => 35000, 'dept' => 'Maintenance'],
            ['title' => 'Supervisor', 'count' => 4, 'salary_min' => 35000, 'salary_max' => 45000, 'dept' => 'Production'],
            ['title' => 'Engineer', 'count' => 3, 'salary_min' => 45000, 'salary_max' => 55000, 'dept' => 'Engineering'],
            ['title' => 'Manager', 'count' => 3, 'salary_min' => 55000, 'salary_max' => 65000, 'dept' => 'Administration'],
            ['title' => 'Contract Worker', 'count' => 5, 'salary_min' => 12000, 'salary_max' => 16000, 'dept' => 'Contract'],
        ];
        
        $empNum = 1;
        $contractorIds = [];
        
        foreach ($roles as $role) {
            for ($i = 0; $i < $role['count']; $i++) {
                $empCode = 'EMP' . str_pad($empNum, 3, '0', STR_PAD_LEFT);
                $salary = rand($role['salary_min'], $role['salary_max']);
                $contractorId = null;
                
                // Assign contractor for contract workers
                if ($role['title'] === 'Contract Worker') {
                    if (empty($contractorIds)) {
                        $contractorIds = DB::table('contractors')->where('tenant_id', $this->tenantId)->pluck('id')->toArray();
                    }
                    $contractorId = $contractorIds[array_rand($contractorIds)] ?? null;
                }
                
                $employeeId = DB::table('workforce_employee')->insertGetId([
                    'tenant_id' => $this->tenantId,
                    'branch_id' => $this->branchId,
                    'employee_code' => $empCode,
                    'name' => $this->generateName($empNum),
                    'designation' => $role['title'],
                    'department' => $role['dept'],
                    'date_of_joining' => Carbon::create(2025, rand(1, 12), rand(1, 28)),
                    'basic_salary' => $salary,
                    'pf_number' => 'PF/' . $this->branchId . '/' . $empCode,
                    'esi_number' => 'ESI/' . $this->branchId . '/' . $empCode,
                    'contractor_id' => $contractorId,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->employees[] = [
                    'id' => $employeeId,
                    'code' => $empCode,
                    'name' => $this->generateName($empNum),
                    'salary' => $salary,
                    'contractor_id' => $contractorId,
                ];
                
                $empNum++;
            }
        }
        
        $this->command->info('✓ Created ' . count($this->employees) . ' employees');
    }
    
    private function seedContractors(): void
    {
        $this->command->info('PHASE 8a: Creating 3 contractors...');
        
        $contractors = [
            ['name' => 'ABC Manpower Services', 'license' => 'CLRA/TN/2025/001'],
            ['name' => 'XYZ Labour Contractors', 'license' => 'CLRA/TN/2025/002'],
            ['name' => 'Global Workforce Solutions', 'license' => 'CLRA/TN/2025/003'],
        ];
        
        foreach ($contractors as $contractor) {
            DB::table('contractors')->insert([
                'tenant_id' => $this->tenantId,
                'contractor_name' => $contractor['name'],
                'license_number' => $contractor['license'],
                'contact_person' => 'Manager',
                'phone' => '9' . rand(100000000, 999999999),
                'address' => 'Chennai, Tamil Nadu',
                'license_valid_from' => '2025-01-01',
                'license_valid_to' => '2026-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('✓ Created 3 contractors');
    }
    
    private function seedAttendance(): void
    {
        $this->command->info('PHASE 2: Creating attendance for January 2026...');
        
        $daysInMonth = 31;
        $records = 0;
        
        foreach ($this->employees as $emp) {
            $workingDays = rand(22, 27);
            $leaveDays = rand(1, 3);
            $absentDays = $daysInMonth - $workingDays - $leaveDays;
            
            $dayTypes = array_merge(
                array_fill(0, $workingDays, 'present'),
                array_fill(0, $leaveDays, 'leave'),
                array_fill(0, $absentDays, 'absent')
            );
            shuffle($dayTypes);
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create(2026, 1, $day);
                
                // Skip Sundays
                if ($date->dayOfWeek === 0) {
                    continue;
                }
                
                $status = $dayTypes[$day - 1] ?? 'present';
                
                DB::table('workforce_attendance')->insert([
                    'tenant_id' => $this->tenantId,
                    'employee_id' => $emp['id'],
                    'attendance_date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $records++;
            }
        }
        
        $this->command->info('✓ Created ' . $records . ' attendance records');
    }
    
    private function seedBonusRecords(): void
    {
        $this->command->info('PHASE 4: Creating bonus records...');
        
        $records = 0;
        
        for ($i = 0; $i < 15; $i++) {
            $emp = $this->employees[array_rand($this->employees)];
            $bonusAmount = rand(5000, 25000);
            $bonusPercentage = rand(8, 20);
            
            DB::table('bonus_records')->insert([
                'tenant_id' => $this->tenantId,
                'employee_id' => $emp['id'],
                'financial_year' => '2025-26',
                'bonus_percentage' => $bonusPercentage,
                'bonus_amount' => $bonusAmount,
                'payment_date' => '2026-01-15',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $records++;
        }
        
        $this->command->info('✓ Created ' . $records . ' bonus records');
    }
    
    private function seedAccidents(): void
    {
        $this->command->info('PHASE 5: Creating accident records...');
        
        $accidents = [
            ['severity' => 'Minor', 'description' => 'Hand injury while operating machine'],
            ['severity' => 'Minor', 'description' => 'Slip and fall in production area'],
            ['severity' => 'Serious', 'description' => 'Machine malfunction causing injury'],
        ];
        
        $records = 0;
        
        foreach ($accidents as $accident) {
            $emp = $this->employees[array_rand($this->employees)];
            
            DB::table('incident_documents')->insert([
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'employee_id' => $emp['id'],
                'incident_type' => 'Accident',
                'incident_date' => '2026-01-' . rand(5, 25),
                'severity' => $accident['severity'],
                'description' => $accident['description'],
                'action_taken' => 'First aid provided, reported to authorities',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $records++;
        }
        
        $this->command->info('✓ Created ' . $records . ' accident records');
    }
    
    private function seedCLRAReturns(): void
    {
        $this->command->info('PHASE 6: Creating CLRA returns...');
        
        DB::table('clra_returns')->insert([
            [
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'return_type' => 'Half-Yearly',
                'period_from' => '2025-07-01',
                'period_to' => '2025-12-31',
                'total_contractors' => 3,
                'total_workers' => 5,
                'total_wages_paid' => 384000,
                'submitted_date' => '2026-01-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'return_type' => 'Annual',
                'period_from' => '2025-01-01',
                'period_to' => '2025-12-31',
                'total_contractors' => 3,
                'total_workers' => 5,
                'total_wages_paid' => 768000,
                'submitted_date' => '2026-01-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        $this->command->info('✓ Created 2 CLRA returns');
    }
    
    private function generateName(int $num): string
    {
        $firstNames = ['Raj', 'Kumar', 'Vijay', 'Arun', 'Suresh', 'Ramesh', 'Ganesh', 'Prakash', 'Dinesh', 'Mahesh', 'Karthik', 'Ravi', 'Sanjay', 'Ajay', 'Deepak', 'Manoj', 'Anand', 'Venkat', 'Balaji', 'Senthil'];
        $lastNames = ['Kumar', 'Raj', 'Prasad', 'Reddy', 'Sharma', 'Singh', 'Patel', 'Gupta', 'Verma', 'Rao', 'Nair', 'Iyer', 'Menon', 'Pillai', 'Das', 'Joshi', 'Desai', 'Mehta', 'Shah', 'Naik'];
        
        return $firstNames[($num - 1) % count($firstNames)] . ' ' . $lastNames[($num - 1) % count($lastNames)];
    }
    
    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  REALISTIC COMPLIANCE DATA SEEDING COMPLETE');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('Tenant ID: ' . $this->tenantId);
        $this->command->info('Branch ID: ' . $this->branchId);
        $this->command->info('Period: January 2026');
        $this->command->info('');
        $this->command->info('RECORDS CREATED:');
        $this->command->info('  Employees: ' . DB::table('workforce_employee')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('  Contractors: ' . DB::table('contractors')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('  Attendance: ' . DB::table('workforce_attendance')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('  Bonus Records: ' . DB::table('bonus_records')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('  Accidents: ' . DB::table('incident_documents')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('  CLRA Returns: ' . DB::table('clra_returns')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('');
        $this->command->info('✓ All forms should now populate with realistic data');
        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
