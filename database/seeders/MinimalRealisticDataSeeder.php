<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MinimalRealisticDataSeeder extends Seeder
{
    private int $tenantId;
    private int $branchId;
    private array $employees = [];
    
    public function run(): void
    {
        // Get FULL tenant and branch
        $this->tenantId = DB::table('tenants')->where('subscription_type', 'FULL')->value('id');
        $this->branchId = DB::table('branches')->where('tenant_id', $this->tenantId)->value('id');
        
        if (!$this->tenantId || !$this->branchId) {
            $this->command->error('FULL tenant or branch not found.');
            return;
        }
        
        $this->command->info('Seeding data for Tenant ID: ' . $this->tenantId . ', Branch ID: ' . $this->branchId);
        
        // Clear existing
        DB::table('workforce_attendance')->where('tenant_id', $this->tenantId)->delete();
        DB::table('workforce_employee')->where('tenant_id', $this->tenantId)->delete();
        
        $this->seedEmployees();
        $this->seedAttendance();
        
        $this->printSummary();
    }
    
    private function seedEmployees(): void
    {
        $this->command->info('Creating 35 employees...');
        
        $roles = [
            ['title' => 'Helper', 'count' => 8, 'salary_min' => 14000, 'salary_max' => 18000],
            ['title' => 'Operator', 'count' => 6, 'salary_min' => 18000, 'salary_max' => 25000],
            ['title' => 'Technician', 'count' => 6, 'salary_min' => 25000, 'salary_max' => 35000],
            ['title' => 'Supervisor', 'count' => 4, 'salary_min' => 35000, 'salary_max' => 45000],
            ['title' => 'Engineer', 'count' => 3, 'salary_min' => 45000, 'salary_max' => 55000],
            ['title' => 'Manager', 'count' => 3, 'salary_min' => 55000, 'salary_max' => 65000],
            ['title' => 'Worker', 'count' => 5, 'salary_min' => 12000, 'salary_max' => 16000],
        ];
        
        $empNum = 1;
        
        foreach ($roles as $role) {
            for ($i = 0; $i < $role['count']; $i++) {
                $empCode = 'EMP' . str_pad($empNum, 3, '0', STR_PAD_LEFT);
                $salary = rand($role['salary_min'], $role['salary_max']);
                
                $employeeId = DB::table('workforce_employee')->insertGetId([
                    'tenant_id' => $this->tenantId,
                    'branch_id' => $this->branchId,
                    'employee_code' => $empCode,
                    'name' => $this->generateName($empNum),
                    'designation' => $role['title'],
                    'department' => 'Production',
                    'date_of_joining' => Carbon::create(2025, rand(1, 12), rand(1, 28)),
                    'basic_salary' => $salary,
                    'pf_number' => 'PF/' . $this->branchId . '/' . $empCode,
                    'esi_number' => 'ESI/' . $this->branchId . '/' . $empCode,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->employees[] = ['id' => $employeeId, 'code' => $empCode, 'salary' => $salary];
                $empNum++;
            }
        }
        
        $this->command->info('✓ Created ' . count($this->employees) . ' employees');
    }
    
    private function seedAttendance(): void
    {
        $this->command->info('Creating attendance for January 2026...');
        
        $records = 0;
        
        foreach ($this->employees as $emp) {
            $workingDays = rand(22, 27);
            $leaveDays = rand(1, 3);
            $absentDays = 31 - $workingDays - $leaveDays;
            
            $dayTypes = array_merge(
                array_fill(0, $workingDays, 'present'),
                array_fill(0, $leaveDays, 'leave'),
                array_fill(0, $absentDays, 'absent')
            );
            shuffle($dayTypes);
            
            for ($day = 1; $day <= 31; $day++) {
                $date = Carbon::create(2026, 1, $day);
                
                if ($date->dayOfWeek === 0) continue;
                
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
    
    private function generateName(int $num): string
    {
        $firstNames = ['Raj', 'Kumar', 'Vijay', 'Arun', 'Suresh', 'Ramesh', 'Ganesh', 'Prakash', 'Dinesh', 'Mahesh'];
        $lastNames = ['Kumar', 'Raj', 'Prasad', 'Reddy', 'Sharma', 'Singh', 'Patel', 'Gupta', 'Verma', 'Rao'];
        
        return $firstNames[($num - 1) % count($firstNames)] . ' ' . $lastNames[($num - 1) % count($lastNames)];
    }
    
    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  REALISTIC DATA SEEDING COMPLETE');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('Tenant ID: ' . $this->tenantId);
        $this->command->info('Branch ID: ' . $this->branchId);
        $this->command->info('Period: January 2026');
        $this->command->info('');
        $this->command->info('RECORDS CREATED:');
        $this->command->info('  Employees: ' . DB::table('workforce_employee')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('  Attendance: ' . DB::table('workforce_attendance')->where('tenant_id', $this->tenantId)->count());
        $this->command->info('');
        $this->command->info('✓ Forms can now be generated with realistic data');
        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
