<?php

namespace App\Services\Compliance\FormGenerator;

class ContractorMasterGenerator extends BaseFormGenerator
{
    protected string $formCode = 'CONTRACTOR_MASTER';
    protected string $view = 'compliance.forms.contractor_master';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'contractor_name' => $record->contractor_name ?? 'N/A',
                'license_number' => $record->license_number ?? 'N/A',
                'registration_date' => $record->registration_date ?? 'N/A',
                'address' => $record->address ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'Contractor Master Register',
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
