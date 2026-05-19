<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'id' => 1,
                'tenant_id' => 1,
                'branch_name' => 'Main Factory',
                'unit_name' => 'Unit A',
                'factory_license_number' => 'FL/2024/001/A',
                'address' => '123 Industrial Area, Bangalore, KA 560001',
                'pf_code' => 'KA/DEMO/00001/A',
                'esi_code' => 'KA/ESI/001/A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tenant_id' => 1,
                'branch_name' => 'Secondary Unit',
                'unit_name' => 'Unit B',
                'factory_license_number' => 'FL/2024/001/B',
                'address' => '456 Industrial Area, Bangalore, KA 560002',
                'pf_code' => 'KA/DEMO/00001/B',
                'esi_code' => 'KA/ESI/001/B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'tenant_id' => 2,
                'branch_name' => 'Head Office',
                'unit_name' => 'HO',
                'factory_license_number' => 'FL/2024/002/HO',
                'address' => '789 Business Park, Bangalore, KA 560003',
                'pf_code' => 'KA/DEMO/00002/HO',
                'esi_code' => 'KA/ESI/002/HO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
