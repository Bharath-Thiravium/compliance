<?php

namespace App\Services\Compliance\Registry;

class FormTemplateRegistry
{
    private static array $templates = [
        'FormXII' => 'compliance.forms.form_xii',
        'FormXIII' => 'compliance.forms.form_xiii',
        'FormXIV' => 'compliance.forms.form_xiv',
        'FormXVI' => 'compliance.forms.form_xvi',
        'FormXVII' => 'compliance.forms.form_xvii',
        'FormXIX' => 'compliance.forms.form_xix',
        'FormXX' => 'compliance.forms.form_xx',
        'FormXXI' => 'compliance.forms.form_xxi',
        'FormXXII' => 'compliance.forms.form_xxii',
        'FormXXIII' => 'compliance.forms.form_xxiii',

        'FormA' => 'compliance.forms.form_a',
        'FormC' => 'compliance.forms.form_c',
        'FormD' => 'compliance.forms.form_d',
        'FormDER' => 'compliance.forms.form_d_er',
        'Form11' => 'compliance.forms.form_11',

        'ESIForm12' => 'compliance.forms.esi_form_12',
        'EPFInspection' => 'compliance.forms.epf_inspection',
        'FormB' => 'compliance.forms.form_b',
        'Form2' => 'compliance.forms.form_2',

        'Form8' => 'compliance.forms.form_8',
        'Form10' => 'compliance.forms.form_10',
        'Form12' => 'compliance.forms.form_12',
        'Form17' => 'compliance.forms.form_17',
        'Form18' => 'compliance.forms.form_18',
        'Form25' => 'compliance.forms.form_25',
        'Form26' => 'compliance.forms.form_26',
        'Form26A' => 'compliance.forms.form_26a',
        'HazardReg' => 'compliance.forms.hazard_reg',
        'ShopsFormC' => 'compliance.forms.shops_form_c',
        'ShopsUnpaid' => 'compliance.forms.shops_unpaid',
        'ShopsForm12' => 'compliance.forms.shops_form_12',
        'SHOPS_FORM_12' => 'compliance.forms.shops_form_12',
        'SHOPS_FORM_13' => 'compliance.forms.shops_form_13',

        'ShopsForm13' => 'compliance.forms.shops_form_13',

        'ShopsFines' => 'compliance.forms.shops_fines',
        'ShopsFormVI' => 'compliance.forms.shops_form_vi',

    ];

    private static array $aliases = [
        'FORM_XII'       => 'FormXII',
        'FORM_XIII'      => 'FormXIII',
        'FORM_XIV'       => 'FormXIV',
        'FORM_XVI'       => 'FormXVI',
        'FORM_XVII'      => 'FormXVII',
        'FORM_XIX'       => 'FormXIX',
        'FORM_XX'        => 'FormXX',
        'FORM_XXI'       => 'FormXXI',
        'FORM_XXII'      => 'FormXXII',
        'FORM_XXIII'     => 'FormXXIII',
        'FORM_A'         => 'FormA',
        'FORM_C'         => 'FormC',
        'FORM_D'         => 'FormD',
        'FORM_D_ER'      => 'FormDER',
        'FORM_11'        => 'Form11',
        'ESI_FORM_12'    => 'ESIForm12',
        'EPF_INSPECTION' => 'EPFInspection',
        'FORM_B'         => 'FormB',
        'FORM_2'         => 'Form2',
        'FORM_8'         => 'Form8',
        'FORM_10'        => 'Form10',
        'FORM_12'        => 'Form12',
        'FORM_17'        => 'Form17',
        'FORM_18'        => 'Form18',
        'FORM_25'        => 'Form25',
        'FORM_26'        => 'Form26',
        'FORM_26A'       => 'Form26A',
        'HAZARD_REG'     => 'HazardReg',
        'SHOPS_FORM_C'   => 'ShopsFormC',
        'SHOPS_UNPAID'   => 'ShopsUnpaid',
        'SHOPS_FORM_12'  => 'ShopsForm12',
        'SHOPS_FORM_13'  => 'ShopsForm13',
        'SHOPS_FINES'    => 'ShopsFines',
        'SHOPS_FORM_VI'  => 'ShopsFormVI',
    ];

    public static function resolve(string $formCode): string
    {
        $normalized = self::$aliases[$formCode] ?? $formCode;

        if (isset(self::$templates[$normalized])) {
            return self::$templates[$normalized];
        }
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $normalized));
        return 'compliance.forms.' . $snake;

    }

    public static function getAll(): array
    {
        return self::$templates;
    }

    public static function exists(string $formCode): bool
    {
        return isset(self::$templates[$formCode]);
    }
}
