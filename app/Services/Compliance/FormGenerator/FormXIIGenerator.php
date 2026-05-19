<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XII';
    protected string $view = 'compliance.forms.form_xii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $rows[] = [
                'contractor_name' => $record['contractor_name'] ?? '',
                'contractor_address' => $record['address'] ?? '',
                'nature_of_work' => 'Contract Labour Work',
                'work_location' => $rawData['branch']['address'] ?? '',
                'contract_from' => $record['license_no'] ?? '',
                'contract_to' => $record['license_expiry'] ?? '',
                'max_workers' => 0,
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XII - Register of Contractors',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
