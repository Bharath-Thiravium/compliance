<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->where('name', 'Demo Compliance Industries Pvt Ltd')->value('id');
        $branchId = DB::table('branches')->where('tenant_id', $tenantId)->value('id');
        
        if (!$tenantId || !$branchId) {
            $this->command->error('Tenant or Branch not found. Run ComprehensiveDemoDataSeeder first.');
            return;
        }

        $employees = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->pluck('id');

        $months = [
            ['from' => '2025-01-01', 'to' => '2025-01-31'],
            ['from' => '2025-02-01', 'to' => '2025-02-28'],
            ['from' => '2025-03-01', 'to' => '2025-03-31'],
        ];

        $totalRecords = 0;

        foreach ($months as $month) {
            $start = Carbon::parse($month['from']);
            $end = Carbon::parse($month['to']);

            for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                foreach ($employees as $employeeId) {
                    $status = rand(1, 100) <= 85 ? 'present' : (rand(1, 100) <= 50 ? 'absent' : 'leave');

                    DB::table('workforce_attendance')->insert([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'employee_id' => $employeeId,
                        'attendance_date' => $date->toDateString(),
                        'status' => $status,
                        'remarks' => $status === 'leave' ? 'Casual Leave' : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalRecords++;
                }
            }
        }

        $this->command->info("✓ Created {$totalRecords} Attendance Records");
    }
}
