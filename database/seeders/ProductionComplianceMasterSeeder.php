<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionComplianceMasterSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('compliance_forms_master')->delete();
        DB::table('compliance_sections')->delete();

        // Create 4 sections
        $sections = [
            ['section_code' => 'FACTORIES', 'section_name' => 'Factories Act', 'is_active' => true],
            ['section_code' => 'CLRA', 'section_name' => 'CLRA', 'is_active' => true],
            ['section_code' => 'SHOPS', 'section_name' => 'Shops & Establishments', 'is_active' => true],
            ['section_code' => 'SOCIAL_SECURITY', 'section_name' => 'Social Security & Inspection', 'is_active' => true],
        ];

        foreach ($sections as $section) {
            DB::table('compliance_sections')->insert([
                'section_code' => $section['section_code'],
                'section_name' => $section['section_name'],
                'is_active' => $section['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $factoriesId = DB::table('compliance_sections')->where('section_code', 'FACTORIES')->value('id');
        $clraId = DB::table('compliance_sections')->where('section_code', 'CLRA')->value('id');
        $shopsId = DB::table('compliance_sections')->where('section_code', 'SHOPS')->value('id');
        $socialId = DB::table('compliance_sections')->where('section_code', 'SOCIAL_SECURITY')->value('id');

        // FACTORIES ACT - 9 forms (CORRECTED: Removed CLRA forms)
        $factoriesForms = [
            ['form_code' => 'FORM_B', 'form_name' => 'Register of Wages (Form B)', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_10', 'form_name' => 'Overtime Register (Form 10)', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_25', 'form_name' => 'Muster Roll (Form 25)', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_8', 'form_name' => 'Accident Register (Form 8)', 'act_type' => 'Factories', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'FORM_11', 'form_name' => 'Notice of Accident (Form 11)', 'act_type' => 'Factories', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'FORM_12', 'form_name' => 'Register of Adult Workers (Form 12)', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_17', 'form_name' => 'Health Register (Form 17)', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_2', 'form_name' => 'Notice of Manager (Form 2)', 'act_type' => 'Factories', 'frequency' => 'Event', 'priority' => 'Medium'],
            ['form_code' => 'FORM_18', 'form_name' => 'Register of Dangerous Occurrences (Form 18)', 'act_type' => 'Factories', 'frequency' => 'Event', 'priority' => 'High'],
        ];

        // CLRA - 17 forms (CORRECTED: Added forms from Factories section)
        $clraForms = [
            ['form_code' => 'FORM_XIII', 'form_name' => 'Register of Contractors (Form XIII)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_XIV', 'form_name' => 'Register of Workmen (Form XIV)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_XII', 'form_name' => 'Employment Card (Form XII)', 'act_type' => 'CLRA', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'FORM_XVI', 'form_name' => 'Register of Fines (Form XVI)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'FORM_XVII', 'form_name' => 'Register of Deductions (Form XVII)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'FORM_XIX', 'form_name' => 'Register of Advances (Form XIX)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'FORM_XXI', 'form_name' => 'Register of Leave (Form XXI)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_XXIII', 'form_name' => 'Contractor Wage Register (Form XXIII)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_XXIV', 'form_name' => 'Contractor Muster Roll (Form XXIV)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'FORM_XXV', 'form_name' => 'Contractor Overtime Register (Form XXV)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'CLRA_LICENSE', 'form_name' => 'CLRA License Application', 'act_type' => 'CLRA', 'frequency' => 'Annual', 'priority' => 'High'],
            ['form_code' => 'FORM_XX', 'form_name' => 'Register of Unpaid Wages (Form XX)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'FORM_XXII', 'form_name' => 'Register of Loans (Form XXII)', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'FORM_26', 'form_name' => 'Accident Report (Form 26)', 'act_type' => 'CLRA', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'FORM_26A', 'form_name' => 'Dangerous Occurrence Report (Form 26A)', 'act_type' => 'CLRA', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'CONTRACTOR_MASTER', 'form_name' => 'Contractor Master Register', 'act_type' => 'CLRA', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'CLRA_RETURN', 'form_name' => 'CLRA Half-Yearly Return', 'act_type' => 'CLRA', 'frequency' => 'HalfYearly', 'priority' => 'High'],
        ];

        // SHOPS & ESTABLISHMENTS - 7 forms
        $shopsForms = [
            ['form_code' => 'SHOPS_FORM_1', 'form_name' => 'Register of Employment (Shops Form 1)', 'act_type' => 'Shops', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'SHOPS_FORM_12', 'form_name' => 'Wage Register (Shops Form 12)', 'act_type' => 'Shops', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'SHOPS_FORM_C', 'form_name' => 'Leave Register (Shops Form C)', 'act_type' => 'Shops', 'frequency' => 'Monthly', 'priority' => 'High'],
            ['form_code' => 'SHOPS_FORM_VI', 'form_name' => 'Bonus Register (Shops Form VI)', 'act_type' => 'Shops', 'frequency' => 'Annual', 'priority' => 'High'],
            ['form_code' => 'SHOPS_FINES', 'form_name' => 'Register of Fines (Shops)', 'act_type' => 'Shops', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'SHOPS_UNPAID', 'form_name' => 'Register of Unpaid Wages (Shops)', 'act_type' => 'Shops', 'frequency' => 'Monthly', 'priority' => 'Medium'],
            ['form_code' => 'SHOPS_FORM_13', 'form_name' => 'Inspection Register (Shops Form 13)', 'act_type' => 'Shops', 'frequency' => 'Event', 'priority' => 'High'],
        ];

        // SOCIAL SECURITY & INSPECTION - 3 forms
        $socialForms = [
            ['form_code' => 'ESI_FORM_12', 'form_name' => 'ESI Accident Report (Form 12)', 'act_type' => 'ESI', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'EPF_INSPECTION', 'form_name' => 'EPF Inspection Register', 'act_type' => 'EPF', 'frequency' => 'Event', 'priority' => 'High'],
            ['form_code' => 'HAZARD_REG', 'form_name' => 'Hazard Identification Register', 'act_type' => 'Factories', 'frequency' => 'Monthly', 'priority' => 'High'],
        ];

        // Insert all forms
        foreach ($factoriesForms as $form) {
            DB::table('compliance_forms_master')->insert([
                'section_id' => $factoriesId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'auto_generate' => true,
                'upload_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($clraForms as $form) {
            DB::table('compliance_forms_master')->insert([
                'section_id' => $clraId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'auto_generate' => true,
                'upload_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($shopsForms as $form) {
            DB::table('compliance_forms_master')->insert([
                'section_id' => $shopsId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'auto_generate' => true,
                'upload_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($socialForms as $form) {
            DB::table('compliance_forms_master')->insert([
                'section_id' => $socialId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'auto_generate' => false,
                'upload_only' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
