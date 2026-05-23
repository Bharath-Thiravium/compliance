<?php

namespace App\Services\Compliance\FormApis;

class FormApiServiceFactory
{
    protected static array $apiServices = [
        // CLRA Forms
        'FormXII' => FormXIIApiService::class,
        'FormXIII' => FormXIIIApiService::class,
        'FormXIV' => FormXIVApiService::class,
        'FormXVI' => FormXVIApiService::class,
        'FormXVII' => FormXVIIApiService::class,
        'FormXIX' => FormXIXApiService::class,
        'FormXX' => FormXXApiService::class,
        'FormXXI' => FormXXIApiService::class,
        'FormXXII' => FormXXIIApiService::class,
        'FormXXIII' => FormXXIIIApiService::class,

        // Labour Welfare Forms
        'FormA' => FormAApiService::class,
        'FormC' => FormCApiService::class,
        'FormD' => FormDApiService::class,
        'FormDER' => FormDERApiService::class,

        // Social Security
        'Form11' => Form11ApiService::class,
        'ESIForm12' => ESIForm12ApiService::class,

        // Factories Act
        'FormB' => FormBApiService::class,
        'Form2' => Form2ApiService::class,
        'Form8' => Form8ApiService::class,
        'Form10' => Form10ApiService::class,
        'Form12' => Form12ApiService::class,
        'Form17' => Form17ApiService::class,
        'Form18' => Form18ApiService::class,
        'Form25' => Form25ApiService::class,
        'Form26' => Form26ApiService::class,
        'Form26A' => Form26AApiService::class,
        'HazardReg' => HazardRegApiService::class,

        // Shops & Establishment
        'ShopsFormC' => ShopsFormCApiService::class,
        'ShopsUnpaid' => ShopsUnpaidApiService::class,
        'ShopsForm12' => ShopsForm12ApiService::class,
        'ShopsForm13' => ShopsForm13ApiService::class,
        'ShopsFines' => ShopsFinesApiService::class,
        'ShopsFormVI' => ShopsFormVIApiService::class,
    ];

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

    public static function make(string $formCode): ?BaseFormApiService
    {
        $key = self::$codeMap[$formCode] ?? $formCode;
        $serviceClass = self::$apiServices[$key] ?? null;

        if (!$serviceClass) {
            return null;
        }

        try {
            return app($serviceClass);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function register(string $formCode, string $serviceClass): void
    {
        self::$apiServices[$formCode] = $serviceClass;
    }
}
