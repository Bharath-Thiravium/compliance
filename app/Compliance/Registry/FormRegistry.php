<?php

namespace App\Compliance\Registry;

class FormRegistry
{
    private static array $registry = [
        // Factories Act Forms
        'FORM_B' => [
            'builder' => \App\Compliance\Builders\WageRegisterBuilder::class,
            'template' => 'compliance.forms.form_b',
        ],
        'FORM_10' => [
            'builder' => \App\Compliance\Builders\OvertimeRegisterBuilder::class,
            'template' => 'compliance.forms.form_10',
        ],
        'FORM_25' => [
            'builder' => \App\Compliance\Builders\AttendanceRegisterBuilder::class,
            'template' => 'compliance.forms.form_25',
        ],
        'FORM_12' => [
            'builder' => \App\Compliance\Builders\EmployeeRegisterBuilder::class,
            'template' => 'compliance.forms.form_12',
        ],
        'FORM_2' => [
            'builder' => \App\Compliance\Builders\WorkShiftBuilder::class,
            'template' => 'compliance.forms.form_2',
        ],
        'FORM_7' => [
            'builder' => \App\Compliance\Builders\InspectionRegisterBuilder::class,
            'template' => 'compliance.forms.form_7',
        ],
        'FORM_8' => [
            'builder' => \App\Compliance\Builders\IncidentBuilder::class,
            'template' => 'compliance.forms.form_8',
        ],
        'FORM_11' => [
            'builder' => \App\Compliance\Builders\AccidentRegisterBuilder::class,
            'template' => 'compliance.forms.form_11',
        ],
        'FORM_17' => [
            'builder' => \App\Compliance\Builders\HealthRegisterBuilder::class,
            'template' => 'compliance.forms.form_17',
        ],
        'FORM_18' => [
            'builder' => \App\Compliance\Builders\AccidentReportBuilder::class,
            'template' => 'compliance.forms.form_18',
        ],
        'FORM_26' => [
            'builder' => \App\Compliance\Builders\AccidentRegisterBuilder::class,
            'template' => 'compliance.forms.form_26',
        ],
        'FORM_26A' => [
            'builder' => \App\Compliance\Builders\DangerousOccurrenceBuilder::class,
            'template' => 'compliance.forms.form_26a',
        ],

        // CLRA Forms - Using Service-based Builders
        'FORM_XII' => [
            'builder' => \App\Compliance\Builders\ContractorMasterFormBuilder::class,
            'template' => 'compliance.forms.form_xii',
        ],
        'FORM_XIII' => [
            'builder' => \App\Compliance\Builders\ContractorWorkmenFormBuilder::class,
            'template' => 'compliance.forms.form_xiii',
        ],
        'FORM_XIV' => [
            'builder' => \App\Compliance\Builders\EmploymentCardBuilder::class,
            'template' => 'compliance.forms.form_xiv',
        ],
        'FORM_XVI' => [
            'builder' => \App\Compliance\Builders\ContractorMusterFormBuilder::class,
            'template' => 'compliance.forms.form_xvi',
        ],
        'FORM_XVII' => [
            'builder' => \App\Compliance\Builders\ContractorWageRegisterBuilder::class,
            'template' => 'compliance.forms.form_xvii',
        ],
        'FORM_XIX' => [
            'builder' => \App\Compliance\Builders\ContractorWageSlipBuilder::class,
            'template' => 'compliance.forms.form_xix',
        ],
        'FORM_XX' => [
            'builder' => \App\Compliance\Builders\DeductionRegisterFormBuilder::class,
            'template' => 'compliance.forms.form_xx',
        ],
        'FORM_XXI' => [
            'builder' => \App\Compliance\Builders\FinesRegisterFormBuilder::class,
            'template' => 'compliance.forms.form_xxi',
        ],
        'FORM_XXII' => [
            'builder' => \App\Compliance\Builders\AdvanceRegisterFormBuilder::class,
            'template' => 'compliance.forms.form_xxii',
        ],
        'FORM_XXIII' => [
            'builder' => \App\Compliance\Builders\ContractorOvertimeBuilder::class,
            'template' => 'compliance.forms.form_xxiii',
        ],
        'FORM_XXIV' => [
            'builder' => \App\Compliance\Builders\ContractorHalfYearlyBuilder::class,
            'template' => 'compliance.forms.form_xxiv',
        ],
        'FORM_XXV' => [
            'builder' => \App\Compliance\Builders\PrincipalAnnualBuilder::class,
            'template' => 'compliance.forms.form_xxv',
        ],

        // Shops Act Forms
        'SHOPS_FORM_12' => [
            'builder' => \App\Compliance\Builders\ShopsWageRegisterBuilder::class,
            'template' => 'compliance.forms.shops_form_12',
        ],
        'SHOPS_FORM_13' => [
            'builder' => \App\Compliance\Builders\ShopsLeaveRegisterBuilder::class,
            'template' => 'compliance.forms.shops_form_13',
        ],
        'SHOPS_FORM_1' => [
            'builder' => \App\Compliance\Builders\ShopsEmployeeRegisterBuilder::class,
            'template' => 'compliance.forms.shops_form_1',
        ],
        'SHOPS_FORM_C' => [
            'builder' => \App\Compliance\Builders\BonusRegisterBuilder::class,
            'template' => 'compliance.forms.shops_form_c',
        ],
        'SHOPS_FORM_VI' => [
            'builder' => \App\Compliance\Builders\ShopsHolidayRegisterBuilder::class,
            'template' => 'compliance.forms.shops_form_vi',
        ],
        'SHOPS_FINES' => [
            'builder' => \App\Compliance\Builders\ShopsFinesRegisterBuilder::class,
            'template' => 'compliance.forms.shops_fines',
        ],
        'SHOPS_UNPAID' => [
            'builder' => \App\Compliance\Builders\ShopsUnpaidBonusBuilder::class,
            'template' => 'compliance.forms.shops_unpaid',
        ],

        // Social Security Forms
        'ESI_FORM_12' => [
            'builder' => \App\Compliance\Builders\IncidentBuilder::class,
            'template' => 'compliance.forms.esi_form_12',
        ],
        'EPF_INSPECTION' => [
            'builder' => \App\Compliance\Builders\InspectionRegisterBuilder::class,
            'template' => 'compliance.forms.epf_inspection',
        ],

        // Labour Welfare Forms
        'FORM_A' => [
            'builder' => \App\Compliance\Builders\EmployeeRegisterBuilder::class,
            'template' => 'compliance.forms.form_a',
        ],
        'FORM_C' => [
            'builder' => \App\Compliance\Builders\DeductionRegisterBuilder::class,
            'template' => 'compliance.forms.form_c',
        ],
        'FORM_D' => [
            'builder' => \App\Compliance\Builders\AttendanceRegisterBuilder::class,
            'template' => 'compliance.forms.form_d',
        ],
        'FORM_D_ER' => [
            'builder' => \App\Compliance\Builders\EqualRemunerationBuilder::class,
            'template' => 'compliance.forms.form_d_er',
        ],

        // Contractor Master
        'CONTRACTOR_MASTER' => [
            'builder' => \App\Compliance\Builders\ContractorMasterBuilder::class,
            'template' => 'compliance.forms.contractor_master',
        ],
    ];

    public static function getBuilder(string $formCode): ?string
    {
        return self::$registry[$formCode]['builder'] ?? null;
    }

    public static function getTemplate(string $formCode): ?string
    {
        return self::$registry[$formCode]['template'] ?? null;
    }

    public static function isRegistered(string $formCode): bool
    {
        return isset(self::$registry[$formCode]);
    }

    public static function all(): array
    {
        return self::$registry;
    }
}
