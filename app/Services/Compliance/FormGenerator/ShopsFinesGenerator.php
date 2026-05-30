<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsFinesGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FINES';
    protected string $view = 'compliance.forms.shops_fines';

    protected function prepareData(array $rawData): array
    {
        $seen = [];
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            // Composite dedup: same employee + same date + same amount
            $key = implode('|', [
                $record['employee_code']  ?? $record['employee_name'] ?? '',
                $record['fine_date']      ?? '',
                (string)($record['fine_amount'] ?? ''),
            ]);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? '',
                'father_name'   => $record['father_name']   ?? '',
                'reason'        => $record['reason']        ?? '',
                'cause'         => $record['cause']         ?? '',
                'wages'         => $record['wages']         ?? 0,
                'fine_amount'   => $record['fine_amount']   ?? 0,
                'fine_date'     => $record['fine_date']     ?? '',
                'realized_date' => $record['realized_date'] ?? '',
                'remarks'       => $record['remarks']       ?? '',
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
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? '') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? '',
                'owner_name' => $tenant['owner_name'] ?? $tenant['name'] ?? '',
                'factory_name' => $branch['name'] ?? '',
                'address' => $branch['address'] ?? '',
                'place' => $branch['address'] ?? '',
                'district' => $branch['district'] ?? '',
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
