<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsUnpaidGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_UNPAID';
    protected string $view     = 'compliance.forms.shops_unpaid';

    protected function prepareData(array $rawData): array
    {
        // Read from 'quarters' key — API service uses this name to avoid
        // BaseFormGenerator::generate() running normalizeRecords() on it.
        $quarters = $rawData['quarters'] ?? [];

        $fields = [
            'fines_realisation', 'unpaid_basic', 'unpaid_overtime', 'unpaid_allowance',
            'unpaid_bonus', 'unpaid_gratuity', 'unpaid_other',
            'standing_order_deduction', 'pwa_deduction',
        ];

        $quarterKeys = [
            'march'     => 'quarter_march',
            'june'      => 'quarter_june',
            'september' => 'quarter_september',
            'december'  => 'quarter_december',
        ];

        $data = [];
        foreach ($fields as $field) {
            foreach ($quarterKeys as $label => $key) {
                $data[$field][$label] = $quarters[$key][$field] ?? 0;
            }
        }

        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        // is_nil: true only when every single value across all fields and quarters is zero
        $grandTotal = 0;
        foreach ($data as $fieldValues) {
            $grandTotal += array_sum($fieldValues);
        }

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
            'rows'   => [],        // required by orchestrator row-count logic
            'is_nil' => $grandTotal === 0.0,
        ];
    }
}
