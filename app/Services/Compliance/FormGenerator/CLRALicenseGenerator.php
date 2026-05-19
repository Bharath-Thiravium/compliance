<?php

namespace App\Services\Compliance\FormGenerator;

class CLRALicenseGenerator extends BaseFormGenerator
{
    protected string $formCode = 'CLRA_LICENSE';
    protected string $view = 'compliance.forms.clra_license';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'license_number' => $record->license_number ?? 'N/A',
                'contractor_name' => $record->contractor_name ?? 'N/A',
                'issue_date' => $record->issue_date ?? 'N/A',
                'expiry_date' => $record->expiry_date ?? 'N/A',
                'status' => $record->status ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'License Register',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
