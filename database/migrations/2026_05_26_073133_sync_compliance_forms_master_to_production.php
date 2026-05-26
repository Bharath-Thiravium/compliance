<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('compliance_forms_master')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $forms = [
            // CLRA (section_id=1)
            ['CLRA_LICENSE',      'CLRA License Application',                    1, 1],
            ['CLRA_RETURN',       'CLRA Half-Yearly Return',                     1, 1],
            ['CONTRACTOR_MASTER', 'Contractor Master Register',                  1, 1],
            ['FORM_XII',          'Employment Card (Form XII)',                   1, 1],
            ['FORM_XIII',         'Register of Contractors (Form XIII)',          1, 1],
            ['FORM_XIV',          'Register of Workmen (Form XIV)',               1, 1],
            ['FORM_XVI',          'Register of Fines (Form XVI)',                 1, 1],
            ['FORM_XVII',         'Register of Deductions (Form XVII)',           1, 1],
            ['FORM_XIX',          'Register of Advances (Form XIX)',              1, 1],
            ['FORM_XX',           'Register of Unpaid Wages (Form XX)',           1, 1],
            ['FORM_XXI',          'Register of Leave (Form XXI)',                 1, 1],
            ['FORM_XXII',         'Register of Loans (Form XXII)',                1, 1],
            ['FORM_XXIII',        'Contractor Wage Register (Form XXIII)',        1, 1],
            ['FORM_XXIV',         'Contractor Muster Roll (Form XXIV)',           1, 1],
            ['FORM_XXV',          'Contractor Overtime Register (Form XXV)',      1, 1],
            // Labour Welfare (section_id=2)
            ['FORM_A',            'Register of Wages (Form A)',                   2, 1],
            ['FORM_C',            'Register of Deductions (Form C)',              2, 1],
            ['FORM_D',            'Register of Fines (Form D)',                   2, 1],
            ['FORM_D_ER',         'Equal Remuneration Register (Form D-ER)',      2, 1],
            // Social Security (section_id=3)
            ['EPF_INSPECTION',    'EPF Inspection Register',                      3, 0],
            ['ESI_FORM_12',       'ESI Accident Report (Form 12)',                3, 1],
            ['FORM_11',           'Notice of Accident (Form 11)',                 3, 1],
            // Factories Act (section_id=4)
            ['FORM_B',            'Register of Wages (Form B)',                   4, 1],
            ['FORM_2',            'Notice of Manager (Form 2)',                   4, 1],
            ['FORM_8',            'Accident Register (Form 8)',                   4, 0],
            ['FORM_10',           'Overtime Register (Form 10)',                  4, 1],
            ['FORM_12',           'Register of Adult Workers (Form 12)',          4, 1],
            ['FORM_17',           'Health Register (Form 17)',                    4, 1],
            ['FORM_18',           'Register of Dangerous Occurrences (Form 18)', 4, 1],
            ['FORM_25',           'Muster Roll (Form 25)',                        4, 1],
            ['FORM_26',           'Accident Report (Form 26)',                    4, 1],
            ['FORM_26A',          'Dangerous Occurrence Report (Form 26A)',       4, 1],
            ['HAZARD_REG',        'Hazard Identification Register',               4, 0],
            // Shops & Establishment (section_id=5)
            ['SHOPS_FORM_1',      'Register of Employment (Shops Form 1)',        5, 1],
            ['SHOPS_FORM_12',     'Wage Register (Shops Form 12)',                5, 1],
            ['SHOPS_FORM_13',     'Inspection Register (Shops Form 13)',          5, 0],
            ['SHOPS_FORM_C',      'Leave Register (Shops Form C)',                5, 1],
            ['SHOPS_FORM_VI',     'Bonus Register (Shops Form VI)',               5, 1],
            ['SHOPS_UNPAID',      'Register of Unpaid Wages (Shops)',             5, 1],
            ['SHOPS_FINES',       'Register of Fines (Shops)',                    5, 1],
        ];

        $actTypeMap = [1=>'CLRA', 2=>'Factories', 3=>'EPF', 4=>'Factories', 5=>'Shops'];

        foreach ($forms as [$code, $name, $sectionId, $active]) {
            DB::table('compliance_forms_master')->insert([
                'form_code'  => $code,
                'form_name'  => $name,
                'section_id' => $sectionId,
                'is_active'  => $active,
                'act_type'   => $actTypeMap[$sectionId],
                'frequency'  => 'Monthly',
                'priority'   => 'High',
            ]);
        }
    }

    public function down(): void
    {
        // Cannot restore old data — run from backup if needed
    }
};
