<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsUnpaidGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_UNPAID';
    protected string $view = 'compliance.forms.shops_unpaid';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $fields  = ['fines_realisation', 'unpaid_basic', 'unpaid_overtime', 'unpaid_allowance',
                    'unpaid_bonus', 'unpaid_gratuity', 'unpaid_other', 'standing_order_deduction', 'pwa_deduction'];
        $quarters = ['march' => 'quarter_march', 'june' => 'quarter_june',
                     'september' => 'quarter_september', 'december' => 'quarter_december'];

        $data = [];
        foreach ($fields as $field) {
            foreach ($quarters as $label => $key) {
                $data[$field][$label] = $records[$key][$field] ?? 0;
            }
        }

        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year'] ?? date('Y');
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        $isNil = !array_filter($data, fn($q) => array_sum($q) > 0);

        return [
            'header' => [
                'form_title'         => 'Register of Fines and Unpaid Accumulations',
                'period'             => $this->formatPeriod($month, $year),
                'establishment_name' => $branch['name'] ?? 'N/A',
                'address'            => $branch['address'] ?? 'N/A',
                'tenant'             => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details'     => $tenant,
                'branch'             => $branch,
            ],
            'data'   => $data,
            'is_nil' => $isNil,
        ];
    }
}
