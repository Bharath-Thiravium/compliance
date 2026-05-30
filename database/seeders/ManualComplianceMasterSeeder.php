<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualComplianceMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('compliance_manual_master')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();

        // Defaults come first so individual record fields (is_automatable, is_event_based, etc.) override them
        $defaults = [
            'due_month'         => null,
            'requires_document' => true,
            'is_event_based'    => false,
            'is_automatable'    => false,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        $records = array_map(fn($r) => array_merge($defaults, $r), $this->records());

        DB::table('compliance_manual_master')->insert($records);
    }

    private function records(): array
    {
        return [

            // ---------------------------------------------------------------
            // FORMS (20)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Form B - Register of Adult Workers',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form 12 - Muster Roll cum Wage Register',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form A - Register of Fines',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form C - Register of Deductions',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form D - Register of Advances',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form 2 - Nomination and Declaration Form (EPF)',
                'act_name'        => 'Employees Provident Fund Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Form 11 - Declaration Form for New Joinee (EPF)',
                'act_name'        => 'Employees Provident Fund Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Form 1 - Nomination Form (ESI)',
                'act_name'        => 'Employees State Insurance Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Form 10 - Leave with Wages Register',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form 17 - Register of Overtime',
                'act_name'        => 'Factories Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form 25 - Register of Accidents',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form F - Nomination Form (Gratuity)',
                'act_name'        => 'Payment of Gratuity Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Form I - Notice of Opening (Shops & Establishments)',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Form XIII - Register of Workmen (CLRA)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form XIV - Employment Card (CLRA)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form XVI - Muster Roll (CLRA)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form XIX - Wage Slip (CLRA)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form A - Register of Labour Welfare Fund Contributions',
                'act_name'        => 'Labour Welfare Fund Act',
                'frequency'       => 'half_yearly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Form 18 - Notice of Dangerous Occurrence',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Form 26 - Register of Exemptions',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],

            // ---------------------------------------------------------------
            // REGISTERS (20)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Register of Wages',
                'act_name'        => 'Minimum Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Attendance',
                'act_name'        => 'Factories Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Employment (Contract Labour)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Contractors',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Inspections',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Register of Hazardous Processes Workers',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Leave with Wages',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Compensatory Holidays',
                'act_name'        => 'Factories Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Overtime',
                'act_name'        => 'Minimum Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Bonus Paid',
                'act_name'        => 'Payment of Bonus Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Gratuity',
                'act_name'        => 'Payment of Gratuity Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of EPF Contributions',
                'act_name'        => 'Employees Provident Fund Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of ESI Contributions',
                'act_name'        => 'Employees State Insurance Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Accidents and Dangerous Occurrences',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Fines',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Deductions for Damage or Loss',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Advances',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Register of Welfare Officers',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Safety Officers',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Register of Young Persons',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],

            // ---------------------------------------------------------------
            // RETURNS (16)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Annual Return under Factories Act',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Half-Yearly Return under Factories Act',
                'act_name'        => 'Factories Act',
                'frequency'       => 'half_yearly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under CLRA Act',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Minimum Wages Act',
                'act_name'        => 'Minimum Wages Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Payment of Wages Act',
                'act_name'        => 'Payment of Wages Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Payment of Bonus Act',
                'act_name'        => 'Payment of Bonus Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Shops and Establishments Act',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Half-Yearly Return under ESI Act',
                'act_name'        => 'Employees State Insurance Act',
                'frequency'       => 'half_yearly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Labour Welfare Fund Act',
                'act_name'        => 'Labour Welfare Fund Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Maternity Benefit Act',
                'act_name'        => 'Maternity Benefit Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Equal Remuneration Act',
                'act_name'        => 'Equal Remuneration Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Quarterly Return of EPF Contributions',
                'act_name'        => 'Employees Provident Fund Act',
                'frequency'       => 'quarterly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Contract Labour (Contractor)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Industrial Disputes Act',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Return under Child Labour Act',
                'act_name'        => 'Child and Adolescent Labour Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Half-Yearly Return under Shops and Establishments Act',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'half_yearly',
                'is_event_based'  => false,
            ],

            // ---------------------------------------------------------------
            // REPORTS (12)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Accident Report to Inspector of Factories',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Dangerous Occurrence Report',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
                'is_automatable'  => true,
            ],
            [
                'compliance_name' => 'Occupational Disease Report',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Monthly ESI Contribution Report',
                'act_name'        => 'Employees State Insurance Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Monthly EPF Contribution Report',
                'act_name'        => 'Employees Provident Fund Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Bonus Payment Report',
                'act_name'        => 'Payment of Bonus Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Safety Committee Meeting Report',
                'act_name'        => 'Factories Act',
                'frequency'       => 'quarterly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Fire Safety Inspection Report',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Medical Examination Report of Workers',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Report of Closure of Establishment',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Report of Retrenchment of Workers',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Report of Lay-off of Workers',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],

            // ---------------------------------------------------------------
            // NOTICES (15)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Notice of Commencement of Work (CLRA)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Completion of Work (CLRA)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Change in Factory Manager',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Change in Occupier',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Intended Closure of Factory',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Holidays Display',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Notice of Working Hours Display',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Notice of Minimum Wages Display',
                'act_name'        => 'Minimum Wages Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Notice of Weekly Holiday Display',
                'act_name'        => 'Weekly Holidays Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Notice of Change in Conditions of Service',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Strike',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Lock-out',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Notice of Retrenchment',
                'act_name'        => 'Industrial Disputes Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Abstract of Factories Act Display',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Abstract of Maternity Benefit Act Display',
                'act_name'        => 'Maternity Benefit Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],

            // ---------------------------------------------------------------
            // APPLICATIONS (10)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Application for Factory Registration',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Application for Renewal of Factory Licence',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Application for Amendment of Factory Licence',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Application for Contract Labour Licence (Contractor)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Application for Renewal of Contract Labour Licence',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Application for Registration under CLRA (Principal Employer)',
                'act_name'        => 'CLRA Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Application for Registration under Shops and Establishments Act',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Application for Renewal under Shops and Establishments Act',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Application for Exemption from Working Hours',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Application for Permission to Work Overtime',
                'act_name'        => 'Factories Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],

            // ---------------------------------------------------------------
            // COMPLIANCE ACTIONS (15)
            // ---------------------------------------------------------------
            [
                'compliance_name' => 'Payment of EPF Contribution',
                'act_name'        => 'Employees Provident Fund Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Payment of ESI Contribution',
                'act_name'        => 'Employees State Insurance Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Payment of Labour Welfare Fund Contribution',
                'act_name'        => 'Labour Welfare Fund Act',
                'frequency'       => 'half_yearly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Payment of Professional Tax',
                'act_name'        => 'Professional Tax Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Payment of Minimum Wages',
                'act_name'        => 'Minimum Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Payment of Annual Bonus',
                'act_name'        => 'Payment of Bonus Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Payment of Gratuity on Separation',
                'act_name'        => 'Payment of Gratuity Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Issuance of Wage Slips to Workers',
                'act_name'        => 'Minimum Wages Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Issuance of Appointment Letter to New Employees',
                'act_name'        => 'Shops and Establishments Act',
                'frequency'       => 'event',
                'is_event_based'  => true,
            ],
            [
                'compliance_name' => 'Conduct Safety Committee Meeting',
                'act_name'        => 'Factories Act',
                'frequency'       => 'quarterly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Conduct Mock Fire Drill',
                'act_name'        => 'Factories Act',
                'frequency'       => 'half_yearly',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Annual Medical Examination of Workers',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Renewal of First Aid Box and Supplies',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Display of Compliance Abstracts and Notices',
                'act_name'        => 'Factories Act',
                'frequency'       => 'annual',
                'is_event_based'  => false,
            ],
            [
                'compliance_name' => 'Maintenance of Creche Facility',
                'act_name'        => 'Maternity Benefit Act',
                'frequency'       => 'monthly',
                'is_event_based'  => false,
            ],
        ];
    }
}
