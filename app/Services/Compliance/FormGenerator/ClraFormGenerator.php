<?php

namespace App\Services\Compliance\FormGenerator;

class ClraFormGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIII';
    protected string $view = 'compliance.forms.form_xiii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'worker_name' => $record->name ?? 'N/A',
                'contractor_name' => $record->company_name ?? 'N/A',
                'deployment_start' => $record->deployment_start ?? 'N/A',
                'wage_rate' => $record->wage_rate ?? 0,
                'work_order' => $record->work_order_number ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XIII - Register of Workmen Employed by Contractor',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
