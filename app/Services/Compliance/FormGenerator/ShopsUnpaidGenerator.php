<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsUnpaidGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_UNPAID';
    protected string $view = 'compliance.forms.shops_unpaid';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];

        $data = [
            'fines_realisation' => [
                'march' => $records['quarter_march']['fines_realisation'] ?? 0,
                'june' => $records['quarter_june']['fines_realisation'] ?? 0,
                'september' => $records['quarter_september']['fines_realisation'] ?? 0,
                'december' => $records['quarter_december']['fines_realisation'] ?? 0,
            ],
            'unpaid_basic' => [
                'march' => $records['quarter_march']['unpaid_basic'] ?? 0,
                'june' => $records['quarter_june']['unpaid_basic'] ?? 0,
                'september' => $records['quarter_september']['unpaid_basic'] ?? 0,
                'december' => $records['quarter_december']['unpaid_basic'] ?? 0,
            ],
            'unpaid_overtime' => [
                'march' => $records['quarter_march']['unpaid_overtime'] ?? 0,
                'june' => $records['quarter_june']['unpaid_overtime'] ?? 0,
                'september' => $records['quarter_september']['unpaid_overtime'] ?? 0,
                'december' => $records['quarter_december']['unpaid_overtime'] ?? 0,
            ],
            'unpaid_allowance' => [
                'march' => $records['quarter_march']['unpaid_allowance'] ?? 0,
                'june' => $records['quarter_june']['unpaid_allowance'] ?? 0,
                'september' => $records['quarter_september']['unpaid_allowance'] ?? 0,
                'december' => $records['quarter_december']['unpaid_allowance'] ?? 0,
            ],
            'unpaid_bonus' => [
                'march' => $records['quarter_march']['unpaid_bonus'] ?? 0,
                'june' => $records['quarter_june']['unpaid_bonus'] ?? 0,
                'september' => $records['quarter_september']['unpaid_bonus'] ?? 0,
                'december' => $records['quarter_december']['unpaid_bonus'] ?? 0,
            ],
            'unpaid_gratuity' => [
                'march' => $records['quarter_march']['unpaid_gratuity'] ?? 0,
                'june' => $records['quarter_june']['unpaid_gratuity'] ?? 0,
                'september' => $records['quarter_september']['unpaid_gratuity'] ?? 0,
                'december' => $records['quarter_december']['unpaid_gratuity'] ?? 0,
            ],
            'unpaid_other' => [
                'march' => $records['quarter_march']['unpaid_other'] ?? 0,
                'june' => $records['quarter_june']['unpaid_other'] ?? 0,
                'september' => $records['quarter_september']['unpaid_other'] ?? 0,
                'december' => $records['quarter_december']['unpaid_other'] ?? 0,
            ],
            'standing_order_deduction' => [
                'march' => $records['quarter_march']['standing_order_deduction'] ?? 0,
                'june' => $records['quarter_june']['standing_order_deduction'] ?? 0,
                'september' => $records['quarter_september']['standing_order_deduction'] ?? 0,
                'december' => $records['quarter_december']['standing_order_deduction'] ?? 0,
            ],
            'pwa_deduction' => [
                'march' => $records['quarter_march']['pwa_deduction'] ?? 0,
                'june' => $records['quarter_june']['pwa_deduction'] ?? 0,
                'september' => $records['quarter_september']['pwa_deduction'] ?? 0,
                'december' => $records['quarter_december']['pwa_deduction'] ?? 0,
            ],
        ];

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'Register of Fines and Unpaid Accumulations',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'owner_name' => $tenant['owner_name'] ?? $tenant['name'] ?? 'N/A',
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'data' => $data,
            'is_nil' => empty(array_filter($data, fn($q) => array_sum($q) > 0)),
        ];
    }
}
