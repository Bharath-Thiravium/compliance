<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FullFormsSeeder extends Seeder
{
    public function run(): void
    {
        // Create sections
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

        // Get section IDs
        $factoriesId = DB::table('compliance_sections')->where('section_code', 'FACTORIES')->value('id');
        $clraId = DB::table('compliance_sections')->where('section_code', 'CLRA')->value('id');
        $shopsId = DB::table('compliance_sections')->where('section_code', 'SHOPS')->value('id');

        // Factories Act Forms
        $factoriesForms = [
            ['form_code' => 'FORM_1', 'form_name' => 'Notice of Occupier', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_2', 'form_name' => 'Notice of Manager', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_3', 'form_name' => 'Annual Return', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Annual'],
            ['form_code' => 'FORM_4', 'form_name' => 'Notice of Commencement', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_5', 'form_name' => 'Notice of Change', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_6', 'form_name' => 'Register of Adult Workers', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_7', 'form_name' => 'Register of Child Workers', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_8', 'form_name' => 'Register of Leave', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_9', 'form_name' => 'Register of Overtime', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_10', 'form_name' => 'Overtime Register', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_11', 'form_name' => 'Register of Compensatory Holidays', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_12', 'form_name' => 'Register of Accidents', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_13', 'form_name' => 'Notice of Accident', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_14', 'form_name' => 'Notice of Dangerous Occurrence', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_15', 'form_name' => 'Register of Dangerous Occurrences', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Event'],
            ['form_code' => 'FORM_16', 'form_name' => 'Health Register', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_17', 'form_name' => 'Register of Fines', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_18', 'form_name' => 'Register of Deductions', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_19', 'form_name' => 'Muster Roll', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_20', 'form_name' => 'Register of Wages', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_21', 'form_name' => 'Wage Slip', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_22', 'form_name' => 'Register of Unpaid Wages', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_23', 'form_name' => 'Register of Advances', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_24', 'form_name' => 'Register of Loans', 'priority' => 'Medium', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_25', 'form_name' => 'Register of Employment', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
            ['form_code' => 'FORM_B', 'form_name' => 'Wage Register', 'priority' => 'High', 'act_type' => 'Factories', 'frequency' => 'Monthly'],
        ];

        foreach ($factoriesForms as $form) {
            DB::table('compliance_forms_master')->insertOrIgnore([
                'section_id' => $factoriesId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // CLRA Forms
        $clraForms = [
            ['form_code' => 'CLRA_FORM_1', 'form_name' => 'Application for Registration', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Event'],
            ['form_code' => 'CLRA_FORM_2', 'form_name' => 'Certificate of Registration', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Event'],
            ['form_code' => 'CLRA_FORM_3', 'form_name' => 'Application for License', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Event'],
            ['form_code' => 'CLRA_FORM_4', 'form_name' => 'License', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Event'],
            ['form_code' => 'CLRA_FORM_5', 'form_name' => 'Register of Contractors', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Monthly'],
            ['form_code' => 'CLRA_FORM_6', 'form_name' => 'Employment Card', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Event'],
            ['form_code' => 'CLRA_FORM_7', 'form_name' => 'Service Certificate', 'priority' => 'Medium', 'act_type' => 'CLRA', 'frequency' => 'Event'],
            ['form_code' => 'CLRA_FORM_8', 'form_name' => 'Register of Workmen', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Monthly'],
            ['form_code' => 'CLRA_FORM_9', 'form_name' => 'Muster Roll', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Monthly'],
            ['form_code' => 'CLRA_FORM_10', 'form_name' => 'Register of Wages', 'priority' => 'High', 'act_type' => 'CLRA', 'frequency' => 'Monthly'],
        ];

        foreach ($clraForms as $form) {
            DB::table('compliance_forms_master')->insertOrIgnore([
                'section_id' => $clraId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Shops & Establishments Forms
        $shopsForms = [
            ['form_code' => 'SHOP_FORM_A', 'form_name' => 'Application for Registration', 'priority' => 'High', 'act_type' => 'Shops', 'frequency' => 'Event'],
            ['form_code' => 'SHOP_FORM_B', 'form_name' => 'Certificate of Registration', 'priority' => 'High', 'act_type' => 'Shops', 'frequency' => 'Event'],
            ['form_code' => 'SHOP_FORM_C', 'form_name' => 'Register of Employment', 'priority' => 'High', 'act_type' => 'Shops', 'frequency' => 'Monthly'],
            ['form_code' => 'SHOP_FORM_D', 'form_name' => 'Register of Leave', 'priority' => 'High', 'act_type' => 'Shops', 'frequency' => 'Monthly'],
            ['form_code' => 'SHOP_FORM_E', 'form_name' => 'Register of Overtime', 'priority' => 'Medium', 'act_type' => 'Shops', 'frequency' => 'Monthly'],
            ['form_code' => 'SHOP_FORM_F', 'form_name' => 'Notice of Opening', 'priority' => 'High', 'act_type' => 'Shops', 'frequency' => 'Event'],
        ];

        foreach ($shopsForms as $form) {
            DB::table('compliance_forms_master')->insertOrIgnore([
                'section_id' => $shopsId,
                'form_code' => $form['form_code'],
                'form_name' => $form['form_name'],
                'act_type' => $form['act_type'],
                'frequency' => $form['frequency'],
                'priority' => $form['priority'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
