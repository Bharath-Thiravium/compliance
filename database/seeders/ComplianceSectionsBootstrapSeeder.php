<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceSectionsBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['section_name' => 'Contract Labour Regulation Act', 'section_code' => 'CLRA', 'is_active' => true],
            ['section_name' => 'Labour Welfare', 'section_code' => 'LABOUR_WELFARE', 'is_active' => true],
            ['section_name' => 'Social Security', 'section_code' => 'SOCIAL_SECURITY', 'is_active' => true],
            ['section_name' => 'Factories Act', 'section_code' => 'FACTORIES_ACT', 'is_active' => true],
            ['section_name' => 'Shops & Establishment', 'section_code' => 'SHOPS_ESTABLISHMENT', 'is_active' => true],
        ];

        foreach ($sections as $section) {
            DB::table('compliance_sections')->updateOrInsert(
                ['section_code' => $section['section_code']],
                $section
            );
        }
    }
}
