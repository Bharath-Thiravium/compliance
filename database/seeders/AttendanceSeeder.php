<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $attendance = [];
        $attendanceId = 1;

        $employees = DB::table('workforce_employee')->get();
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if ($currentDate->dayOfWeek !== 0 && $currentDate->dayOfWeek !== 6) {
                    $status = rand(1, 100) <= 85 ? 'present' : (rand(1, 100) <= 50 ? 'absent' : 'leave');

                    $attendance[] = [
                        'id' => $attendanceId++,
                        'tenant_id' => $employee->tenant_id,
                        'employee_id' => $employee->id,
                        'attendance_date' => $currentDate->toDateString(),
                        'status' => $status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Batch insert every 500 records
                    if (count($attendance) >= 500) {
                        DB::table('workforce_attendance')->insert($attendance);
                        $attendance = [];
                    }
                }

                $currentDate->addDay();
            }
        }

        // Insert remaining records
        if (!empty($attendance)) {
            DB::table('workforce_attendance')->insert($attendance);
        }
    }
}
