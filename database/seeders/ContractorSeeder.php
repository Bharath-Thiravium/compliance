<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractorSeeder extends Seeder
{
    public function run(): void
    {
        $contractors = [];
        $contractorId = 1;

        // Create contractors for each tenant
        for ($tenant = 1; $tenant <= 2; $tenant++) {
            for ($i = 1; $i <= 5; $i++) {
                $contractors[] = [
                    'id' => $contractorId++,
                    'tenant_id' => $tenant,
                    'contractor_name' => 'Contractor ' . $i . ' - Tenant ' . $tenant,
                    'license_number' => 'LIC' . str_pad($contractorId, 8, '0', STR_PAD_LEFT),
                    'valid_from' => Carbon::now()->subYear()->toDateString(),
                    'valid_to' => Carbon::now()->addYear()->toDateString(),
                    'max_worker_limit' => rand(10, 100),
                    'pf_code' => 'PF' . str_pad($contractorId, 8, '0', STR_PAD_LEFT),
                    'esi_code' => 'ESI' . str_pad($contractorId, 8, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('contractors')->insert($contractors);
    }
}
