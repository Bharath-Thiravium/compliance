<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollCycleSeeder extends Seeder
{
    public function run(): void
    {
        $cycles = [];
        $cycleId = 1;

        // Create payroll cycles for last 12 months for each tenant
        for ($tenant = 1; $tenant <= 2; $tenant++) {
            for ($month = 12; $month >= 1; $month--) {
                $year = $month <= 3 ? 2024 : 2023;
                $periodFrom = Carbon::create($year, $month, 1);
                $periodTo = $periodFrom->copy()->endOfMonth();

                $cycles[] = [
                    'id' => $cycleId++,
                    'tenant_id' => $tenant,
                    'cycle_name' => $periodFrom->format('F Y'),
                    'period_from' => $periodFrom->toDateString(),
                    'period_to' => $periodTo->toDateString(),
                    'status' => 'processed',
                    'processed_at' => now()->subDays(rand(1, 30)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('workforce_payroll_cycle')->insert($cycles);
    }
}
