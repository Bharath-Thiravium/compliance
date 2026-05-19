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
        'ShopsForm13' => 'compliance.forms.shops_form_13',
        'ShopsFines' => 'compliance.forms.shops_fines',
        'ShopsFormVI' => 'compliance.forms.shops_form_vi',

    ];

    public static function resolve(string $formCode): string
    {
        if (isset(self::$templates[$formCode])) {
            return self::$templates[$formCode];
        }
        // Fallback: convert camelCase to snake_case
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $formCode));
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
