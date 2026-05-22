<?php

namespace App\Services\Compliance\FormGenerator;

/**
 * FormGeneratorFactory - Maps form codes to dedicated generators
 *
 * Aligned with official compliance form catalog.
 * Each form has its own generator for better maintainability and debugging.
 */
class FormGeneratorFactory
{
    protected static array $generatorMap = [
        // Contractor Forms
        'FormXII' => FormXIIGenerator::class,
        'FormXIII' => FormXIIIGenerator::class,
        'FormXIV' => FormXIVGenerator::class,
        'FormXVI' => FormXVIGenerator::class,
        'FormXVII' => FormXVIIGenerator::class,
        'FormXIX' => FormXIXGenerator::class,
        'FormXX' => FormXXGenerator::class,
        'FormXXI' => FormXXIGenerator::class,
        'FormXXII' => FormXXIIGenerator::class,
        'FormXXIII' => FormXXIIIGenerator::class,

        // Master Register Forms
        'FormA' => FormAGenerator::class,
        'FormC' => FormCGenerator::class,
        'FormD' => FormDGenerator::class,
        'FormDER' => FORMDERGenerator::class,

        // Incident Forms
        'Form11' => Form11Generator::class,
        'ESIForm12' => ESIForm12Generator::class,
        'EPFInspection' => EPFInspectionGenerator::class,

        // Payroll Forms
        'FormB' => FormBGenerator::class,
        'Form2' => Form2Generator::class,
        'Form10' => Form10Generator::class,
        'Form12' => Form12Generator::class,
        'Form17' => Form17Generator::class,
        'Form18' => Form18Generator::class,
        'Form25' => Form25Generator::class,
        'Form8' => Form8Generator::class,
        'Form26' => Form26Generator::class,
        'Form26A' => Form26AGenerator::class,
        'HazardReg' => HazardRegisterGenerator::class,

        // Shops Forms
        'ShopsFormC' => ShopsFormCGenerator::class,
        'ShopsUnpaid' => ShopsUnpaidGenerator::class,
        'ShopsForm12' => ShopsForm12Generator::class,
        'ShopsForm13' => ShopsForm13Generator::class,
        'ShopsFines' => ShopsFinesGenerator::class,
        'ShopsFormVI' => ShopsFormVIGenerator::class,
    ];

    // Maps DB form codes (FORM_XII) to factory keys (FormXII)
    protected static array $codeMap = [
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
        'FORM_B'         => 'FormB',
        'FORM_C'         => 'FormC',
        'FORM_D'         => 'FormD',
        'FORM_D_ER'      => 'FormDER',
        'FORM_11'        => 'Form11',
        'ESI_FORM_12'    => 'ESIForm12',
        'EPF_INSPECTION' => 'EPFInspection',
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
        'SHOPS_FORM_12'  => 'ShopsForm12',
        'SHOPS_FORM_13'  => 'ShopsForm13',
        'SHOPS_FORM_C'   => 'ShopsFormC',
        'SHOPS_FORM_VI'  => 'ShopsFormVI',
        'SHOPS_UNPAID'   => 'ShopsUnpaid',
        'SHOPS_FINES'    => 'ShopsFines',
    ];

    public static function make(string $formCode): ?BaseFormGenerator
    {
        $key = self::$codeMap[$formCode] ?? $formCode;

        if (!isset(self::$generatorMap[$key])) {
            return null;
        }

        return new self::$generatorMap[$key]();
    }

    public static function getSupportedForms(): array
    {
        return array_keys(self::$generatorMap);
    }
}
