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
        'EPFInspection' => EPFInspectionApiService::class,

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

    public static function make(string $formCode): ?BaseFormApiService
    {
        $serviceClass = self::$apiServices[$formCode] ?? null;
        
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
