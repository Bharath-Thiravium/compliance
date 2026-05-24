<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceFormsMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Seeding compliance forms master...');

        // Get or create default section
        $section = DB::table('compliance_sections')->first();
        if (!$section) {
            $sectionId = DB::table('compliance_sections')->insertGetId([
                'section_name' => 'Labour Compliance',
                'section_code' => 'LABOUR_COMPLIANCE',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $sectionId = $section->id;
        }

        $forms = [
            // CLRA Forms (10)
            ['code' => 'FORM_XII', 'name' => 'Register of Workmen Employed by Contractor', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XIII', 'name' => 'Employment Card', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XIV', 'name' => 'Muster Roll', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XVI', 'name' => 'Register of Wages', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XVII', 'name' => 'Register of Deductions', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XIX', 'name' => 'Wage Slip', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XX', 'name' => 'Register of Fines', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XXI', 'name' => 'Register of Advances', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XXII', 'name' => 'Register of Overtime', 'act' => 'CLRA', 'frequency' => 'Monthly'],
            ['code' => 'FORM_XXIII', 'name' => 'Half-Yearly Return', 'act' => 'CLRA', 'frequency' => 'HalfYearly'],

            // Labour Welfare Forms (4)
            ['code' => 'FORM_A', 'name' => 'Wage Register', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_C', 'name' => 'Bonus Register', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_D', 'name' => 'Equal Remuneration Register', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_D_ER', 'name' => 'Equal Remuneration Details', 'act' => 'Factories', 'frequency' => 'Monthly'],

            // Social Security Forms (3)
            ['code' => 'FORM_11', 'name' => 'Accident Register', 'act' => 'ESI', 'frequency' => 'Monthly'],
            ['code' => 'ESI_FORM_12', 'name' => 'ESI Accident Report', 'act' => 'ESI', 'frequency' => 'Event'],
            ['code' => 'EPF_INSPECTION', 'name' => 'EPF Inspection Register', 'act' => 'EPF', 'frequency' => 'HalfYearly'],

            // Factories Act Forms (11)
            ['code' => 'FORM_B', 'name' => 'Muster Roll', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_2', 'name' => 'Notice of Periods of Work', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_8', 'name' => 'Health Register', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_10', 'name' => 'Adult Worker Register', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_12', 'name' => 'Register of Advances', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_17', 'name' => 'Health Register', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_18', 'name' => 'Report of Accident', 'act' => 'Factories', 'frequency' => 'Event'],
            ['code' => 'FORM_25', 'name' => 'Muster Roll', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_26', 'name' => 'Register of Accident', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'FORM_26A', 'name' => 'Register of Dangerous Occurrences', 'act' => 'Factories', 'frequency' => 'Monthly'],
            ['code' => 'HAZARD_REG', 'name' => 'Hazard Register', 'act' => 'Factories', 'frequency' => 'Monthly'],

            // Shops & Establishment Forms (6)
            ['code' => 'SHOPS_FORM_12', 'name' => 'Shops Register', 'act' => 'Shops', 'frequency' => 'Monthly'],
            ['code' => 'SHOPS_FORM_13', 'name' => 'Establishment Register', 'act' => 'Shops', 'frequency' => 'Monthly'],
            ['code' => 'SHOPS_FORM_C', 'name' => 'Bonus Register', 'act' => 'Shops', 'frequency' => 'Monthly'],
            ['code' => 'SHOPS_FORM_VI', 'name' => 'Holidays Register', 'act' => 'Shops', 'frequency' => 'Monthly'],
            ['code' => 'SHOPS_UNPAID', 'name' => 'Unpaid Wages Register', 'act' => 'Shops', 'frequency' => 'Monthly'],
            ['code' => 'SHOPS_FINES', 'name' => 'Fines Register', 'act' => 'Shops', 'frequency' => 'Monthly'],
        ];

        $noAutoGenerate = ['Form8', 'HazardReg', 'ShopsForm13', 'EPF_INSPECTION'];

        foreach ($forms as $form) {
            DB::table('compliance_forms_master')->updateOrInsert(
                ['form_code' => $form['code']],
                [
                    'section_id' => $sectionId,
                    'form_name' => $form['name'],
                    'act_type' => $form['act'],
                    'frequency' => $form['frequency'],
                    'priority' => 'Medium',
                    'auto_generate' => in_array($form['code'], $noAutoGenerate) ? 0 : 1,
                    'upload_only' => $form['code'] === 'EPF_INSPECTION' ? 1 : 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

                $this->command->info("✓ Registered: {$form['code']} - {$form['name']}");
        }

        $this->command->info("\n✅ All 34 compliance forms registered successfully!");
    }
}
