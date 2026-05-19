<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tenants')->insert([
            [
                'id' => 1,
                'name' => 'Demo Manufacturing Ltd',
                'establishment_name' => 'Demo Manufacturing Ltd',
                'factory_license_no' => 'FL/2024/001',
                'pf_code' => 'KA/DEMO/00001',
                'esi_code' => 'KA/ESI/001',
                'subscription_type' => 'FULL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Demo Services Pvt Ltd',
                'establishment_name' => 'Demo Services Pvt Ltd',
                'factory_license_no' => 'FL/2024/002',
                'pf_code' => 'KA/DEMO/00002',
                'esi_code' => 'KA/ESI/002',
                'subscription_type' => 'FULL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
