<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsForm12Generator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_12';
    protected string $view = 'compliance.forms.shops_form_12';

    protected function prepareData(array $rawData): array
    {
        $seen = [];
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            // Composite dedup: same employee + same date + same amount
            $key = implode('|', [
                $record['employee_code']  ?? $record['employee_name'] ?? '',
                $record['advance_date']   ?? '',
                (string)($record['advance_amount'] ?? ''),
            ]);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $rows[] = [
                'employee_name' => $record['employee_name'] ?? '',
                'father_name'   => $record['father_name']   ?? '',
                'advance_amount'    => round($record['advance_amount'] ?? 0, 2),
                'advance_date'      => $this->formatDate($record['advance_date'] ?? null),
                'purpose'           => $record['advance_purpose'] ?? '',
                'installments'      => $record['advance_installments'] ?? '',
                'postponements'     => $record['advance_postponements'] ?? '',
                'repaid_date'       => $this->formatDate($record['advance_repaid_date'] ?? null),
                'remarks'           => '',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'SHOPS FORM D - Register of Advances',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? '') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? '',
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}
