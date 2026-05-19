<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsFormCGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_C';
    protected string $view = 'compliance.forms.shops_form_c';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $dob = $record['date_of_birth'] ?? null;
            $age = $dob ? (int)date('Y') - (int)date('Y', strtotime($dob)) : 0;
            $totalDeduction = ($record['tax_deducted'] ?? 0) + ($record['loss_deduction'] ?? 0);
            $netPayable = ($record['bonus_amount'] ?? 0) - $totalDeduction;
            
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'father_name' => $record['father_name'] ?? 'N/A',
                'age_eligible' => $age >= 15 ? 'Yes' : 'No',
                'designation' => $record['designation'] ?? 'N/A',
                'days_worked' => (int)($record['days_worked'] ?? 0),
                'total_wages' => (float)($record['total_wages'] ?? 0),
                'bonus_payable' => (float)($record['bonus_amount'] ?? 0),
                'puja_bonus' => (float)($record['puja_bonus'] ?? 0),
                'interim_bonus' => (float)($record['interim_bonus'] ?? 0),
                'tax_deducted' => (float)($record['tax_deducted'] ?? 0),
                'loss_deduction' => (float)($record['loss_deduction'] ?? 0),
                'total_deduction' => $totalDeduction,
                'net_payable' => $netPayable,
                'amount_paid' => (float)($record['bonus_paid'] ?? 0),
                'payment_date' => $record['bonus_payment_date'] ?? '',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        $totals = [
            'total_wages' => array_sum(array_column($rows, 'total_wages')),
            'bonus_payable' => array_sum(array_column($rows, 'bonus_payable')),
            'puja_bonus' => array_sum(array_column($rows, 'puja_bonus')),
            'interim_bonus' => array_sum(array_column($rows, 'interim_bonus')),
            'tax_deducted' => array_sum(array_column($rows, 'tax_deducted')),
            'loss_deduction' => array_sum(array_column($rows, 'loss_deduction')),
            'total_deduction' => array_sum(array_column($rows, 'total_deduction')),
            'net_payable' => array_sum(array_column($rows, 'net_payable')),
            'amount_paid' => array_sum(array_column($rows, 'amount_paid')),
        ];

        return [
            'header' => [
                'form_title' => 'SHOPS FORM C - Bonus Register',
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
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
