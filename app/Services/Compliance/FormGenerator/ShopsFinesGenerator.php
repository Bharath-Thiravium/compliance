<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsFinesGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FINES';
    protected string $view = 'compliance.forms.shops_fines';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? 'N/A',
                'father_name' => $record['father_name'] ?? 'N/A',
                'reason' => $record['reason'] ?? 'N/A',
                'cause' => $record['cause'] ?? 'N/A',
                'wages' => $record['wages'] ?? 0,
                'fine_amount' => $record['fine_amount'] ?? 0,
                'fine_date' => $record['fine_date'] ?? '',
                'realized_date' => $record['realized_date'] ?? '',
                'remarks' => $record['remarks'] ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, ['fine_amount']);

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'Register of Fines',
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
