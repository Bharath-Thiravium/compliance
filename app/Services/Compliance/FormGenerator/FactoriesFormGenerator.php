<?php

namespace App\Services\Compliance\FormGenerator;

class FactoriesFormGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_B';
    protected string $view = 'compliance.forms.form_b';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'employee_code' => $record->employee_code ?? 'N/A',
                'employee_name' => $record->name ?? 'N/A',
                'designation' => $record->designation ?? 'N/A',
                'basic_earned' => $record->basic_earned ?? 0,
                'da_earned' => $record->da_earned ?? 0,
                'hra_earned' => $record->hra_earned ?? 0,
                'overtime_wages' => $record->overtime_wages ?? 0,
                'gross_salary' => $record->gross_salary ?? 0,
                'pf_employee' => $record->pf_employee ?? 0,
                'esi_employee' => $record->esi_employee ?? 0,
                'total_deductions' => $record->total_deductions ?? 0,
                'net_salary' => $record->net_salary ?? 0,
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'basic_earned', 'da_earned', 'hra_earned', 'overtime_wages',
            'gross_salary', 'pf_employee', 'esi_employee', 'total_deductions', 'net_salary'
        ]);

        return [
            'header' => [
                'form_title' => 'FORM B - Register of Wages',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
}
