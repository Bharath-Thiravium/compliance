<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('compliance_sections')->insert([
            [
                'section_name' => 'Factories Act',
                'section_code' => 'FACTORIES',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_name' => 'CLRA',
                'section_code' => 'CLRA',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_name' => 'Shops & Establishments',
                'section_code' => 'SHOPS',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
