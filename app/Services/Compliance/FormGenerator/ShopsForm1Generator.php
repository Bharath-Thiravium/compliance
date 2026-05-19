<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsForm1Generator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_1';
    protected string $view = 'compliance.forms.shops_form_1';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'employee_code' => $record->employee_code ?? 'N/A',
                'employee_name' => $record->employee_name ?? 'N/A',
                'designation' => $record->designation ?? 'N/A',
                'date_of_joining' => $record->date_of_joining ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'SHOPS FORM 1 - Register of Employment',
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
