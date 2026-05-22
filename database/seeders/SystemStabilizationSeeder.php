<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemStabilizationSeeder extends Seeder
{
    public function run(): void
    {
        // Create tenants
        $minimalTenant = DB::table('tenants')->insertGetId([
            'name' => 'Minimal Industries',
            'subscription_type' => 'MINIMAL',
            'establishment_name' => 'Minimal Industries Pvt Ltd',
            'factory_license_no' => 'TN/MIN/2024/001',
            'pf_code' => 'MIN001',
            'esi_code' => 'MIN001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fullTenant = DB::table('tenants')->insertGetId([
            'name' => 'Full Industries',
            'subscription_type' => 'FULL',
            'establishment_name' => 'Full Industries Pvt Ltd',
            'factory_license_no' => 'TN/FULL/2024/001',
            'pf_code' => 'FULL001',
            'esi_code' => 'FULL001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create branches
        DB::table('branches')->insert([
            [
                'tenant_id' => $minimalTenant,
                'branch_name' => 'Minimal Branch 1',
                'unit_name' => 'Unit 1',
                'address' => '123 Minimal Street, Chennai, Tamil Nadu',
                'factory_license_number' => 'TN/MIN/2024/001',
                'pf_code' => 'MIN001',
                'esi_code' => 'MIN001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tenant_id' => $fullTenant,
                'branch_name' => 'Full Branch 1',
                'unit_name' => 'Unit 1',
                'address' => '456 Full Avenue, Chennai, Tamil Nadu',
                'factory_license_number' => 'TN/FULL/2024/001',
                'pf_code' => 'FULL001',
                'esi_code' => 'FULL001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create users
        DB::table('users')->insert([
            [
                'name' => 'Minimal User',
                'email' => 'minimal@test.com',
                'password' => Hash::make('password'),
                'tenant_id' => $minimalTenant,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Full User',
                'email' => 'full@test.com',
                'password' => Hash::make('password'),
                'tenant_id' => $fullTenant,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create compliance sections
        $sections = [
            ['section_name' => 'Factories Act Forms', 'section_code' => 'FACTORIES', 'is_active' => true],
            ['section_name' => 'CLRA Forms', 'section_code' => 'CLRA', 'is_active' => true],
            ['section_name' => 'Shops & Establishments', 'section_code' => 'SHOPS', 'is_active' => true],
        ];

        foreach ($sections as $section) {
            DB::table('compliance_sections')->insertOrIgnore([
                'section_name' => $section['section_name'],
                'section_code' => $section['section_code'],
                'is_active' => $section['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create sample forms
        $sectionId = DB::table('compliance_sections')->where('section_code', 'FACTORIES')->value('id');
        
        $forms = [
            ['form_code' => 'FORM_B', 'form_name' => 'Register of Wages', 'priority' => 'High'],
            ['form_code' => 'FORM_10', 'form_name' => 'Overtime Register', 'priority' => 'Medium'],
            ['form_code' => 'FORM_25', 'form_name' => 'Muster Roll', 'priority' => 'High'],
        ];

        foreach ($forms as $form) {
            DB::table('compliance_forms_master')->insertOrIgnore([
                'section_id' => $sectionId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'priority' => $form['priority'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}