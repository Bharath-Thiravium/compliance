<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsForm13Generator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_13';
    protected string $view = 'compliance.forms.shops_form_13';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'date_of_joining' => $record['joining_date'] ?? 'N/A',
                'leave_rows' => [
                    [
                        'year' => date('Y', strtotime($record['leave_date'] ?? now())),
                        'leave_credit' => '',
                        'leave_earned' => '',
                        'total_leave' => '',
                        'from_date' => $record['leave_date'] ?? '',
                        'to_date' => '',
                        'leave_days' => '1',
                        'balance' => '',
                        'remarks' => '',
                    ]
                ],
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'SHOPS FORM 13 - Leave Book',
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
            'is_nil' => count($rows) === 0,
        ];
    }
}
