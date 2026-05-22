<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Branch;

class FixDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Fix Tenant data
        Tenant::where('id', 1)->update([
            'establishment_name' => 'Demo Compliance Industries Pvt Ltd',
        ]);

        // Fix Branch data
        Branch::where('id', 1)->update([
            'unit_name' => 'Solar Panel Manufacturing Unit',
            'pf_code' => 'TN/CHE/00001',
            'esi_code' => '33000000000000001',
        ]);

        $this->command->info('Demo data fixed successfully!');
    }
}
