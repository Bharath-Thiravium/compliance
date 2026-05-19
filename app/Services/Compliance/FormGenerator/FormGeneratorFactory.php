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

    public static function make(string $formCode): ?BaseFormGenerator
    {
        if (!isset(self::$generatorMap[$formCode])) {
            return null;
        }

        $generatorClass = self::$generatorMap[$formCode];
        return new $generatorClass();
    }

    public static function getSupportedForms(): array
    {
        return array_keys(self::$generatorMap);
    }
}
