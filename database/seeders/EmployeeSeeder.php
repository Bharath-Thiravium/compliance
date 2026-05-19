<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [];
        $employeeId = 1;

        // Generate 50 employees for tenant 1, branch 1
        for ($i = 1; $i <= 25; $i++) {
            $employees[] = [
                'id' => $employeeId++,
                'tenant_id' => 1,
                'branch_id' => 1,
                'employee_code' => 'EMP' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'pf_number' => 'PF' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'esi_number' => 'ESI' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'date_of_joining' => now()->subMonths(rand(1, 24))->toDateString(),
                'designation' => ['Manager', 'Supervisor', 'Operator', 'Helper', 'Technician'][rand(0, 4)],
                'department' => ['Production', 'Quality', 'Maintenance', 'HR', 'Finance'][rand(0, 4)],
                'basic_salary' => rand(15000, 50000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Generate 25 employees for tenant 1, branch 2
        for ($i = 26; $i <= 40; $i++) {
            $employees[] = [
                'id' => $employeeId++,
                'tenant_id' => 1,
                'branch_id' => 2,
                'employee_code' => 'EMP' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'pf_number' => 'PF' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'esi_number' => 'ESI' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'date_of_joining' => now()->subMonths(rand(1, 24))->toDateString(),
                'designation' => ['Manager', 'Supervisor', 'Operator', 'Helper', 'Technician'][rand(0, 4)],
                'department' => ['Production', 'Quality', 'Maintenance', 'HR', 'Finance'][rand(0, 4)],
                'basic_salary' => rand(15000, 50000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Generate 20 employees for tenant 2, branch 3
        for ($i = 41; $i <= 60; $i++) {
            $employees[] = [
                'id' => $employeeId++,
                'tenant_id' => 2,
                'branch_id' => 3,
                'employee_code' => 'EMP' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'pf_number' => 'PF' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'esi_number' => 'ESI' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'date_of_joining' => now()->subMonths(rand(1, 24))->toDateString(),
                'designation' => ['Manager', 'Supervisor', 'Operator', 'Helper', 'Technician'][rand(0, 4)],
                'department' => ['Production', 'Quality', 'Maintenance', 'HR', 'Finance'][rand(0, 4)],
                'basic_salary' => rand(15000, 50000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('workforce_employee')->insert($employees);
    }
}
