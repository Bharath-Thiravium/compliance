<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractLabourSeeder extends Seeder
{
    public function run(): void
    {
        $contractLabour = [];
        $contractId = 1;

        $contractors = DB::table('contractors')->get();
        $employees = DB::table('workforce_employee')->get();

        foreach ($contractors as $contractor) {
            // Assign 5-10 employees to each contractor
            $assignCount = rand(5, 10);
            $tenantEmployees = $employees->where('tenant_id', $contractor->tenant_id)->shuffle();

            for ($i = 0; $i < $assignCount && $i < count($tenantEmployees); $i++) {
                $employee = $tenantEmployees[$i];

                $contractLabour[] = [
                    'id' => $contractId++,
                    'tenant_id' => $contractor->tenant_id,
                    'contractor_id' => $contractor->id,
                    'employee_id' => $employee->id,
                    'deployment_location' => 'Location ' . rand(1, 5),
                    'wage_rate' => rand(300, 800),
                    'employment_start' => Carbon::now()->subMonths(rand(1, 12))->toDateString(),
                    'employment_end' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('contract_labour')->insert($contractLabour);
    }
}
