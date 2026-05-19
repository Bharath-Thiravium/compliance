<?php

namespace App\Services\Compliance\FormGenerator;

class FormXVIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XVII';
    protected string $view     = 'compliance.forms.form_xvii';

    protected function prepareData(array $rawData): array
    {
        $rows   = [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        foreach ($rawData['records'] ?? [] as $record) {
            $record     = $this->normalizeRecord($record);
            $gross      = (float) ($record['gross_salary'] ?? 0);
            $pf         = (float) ($record['pf_employee']  ?? 0);
            $esi        = (float) ($record['esi_employee'] ?? 0);
            $pt         = (float) ($record['pt_deduction'] ?? $record['professional_tax'] ?? 0);
            $totalDeduct = $pf + $esi + $pt + (float) ($record['other_deductions'] ?? 0);

            $rows[] = [
                'employee_code'  => $record['employee_code']  ?? '',
                'name'           => $record['name']           ?? '',
                'father_name'    => $record['father_name']    ?? '',
                'designation'    => $record['designation']    ?? '',
                'uan'            => $record['uan']            ?? $record['pf_number'] ?? '',
                'esi_number'     => $record['esi_number']     ?? '',
                'days_worked'    => $record['days_worked']    ?? $record['total_days_worked'] ?? 0,
                'unit_work'      => '',
                'daily_rate'     => (float) ($record['daily_rate']   ?? 0),
                'basic_wages'    => (float) ($record['basic_earned'] ?? 0),
                'da'             => (float) ($record['da_earned']    ?? 0),
                'hra'            => (float) ($record['hra_earned']   ?? 0),
                'overtime'       => (float) ($record['overtime_wages'] ?? 0),
                'other_cash'     => (float) ($record['other_allowances'] ?? 0),
                'gross_salary'   => $gross,
                'pf'             => $pf,
                'esi'            => $esi,
                'pt'             => $pt,
                'other_deductions' => (float) ($record['other_deductions'] ?? 0),
                'total_deductions' => $totalDeduct,
                'net_amount'     => (float) ($record['net_salary'] ?? ($gross - $totalDeduct)),
                'payment_date'   => $record['payment_date']   ?? '',
                'remarks'        => $record['remarks']        ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'days_worked', 'basic_wages', 'da', 'hra', 'overtime', 'other_cash',
            'gross_salary', 'pf', 'esi', 'pt', 'other_deductions', 'total_deductions', 'net_amount',
        ]);

        return [
            'header' => [
                'contractor_name'    => $tenant['name']    ?? '',
                'establishment_name' => $branch['name']    ?? '',
                'principal_employer' => $tenant['name']    ?? '',
                'work_nature'        => 'Manufacturing',
                'work_location'      => $branch['address'] ?? $branch['name'] ?? '',
                'wage_period'        => $this->formatPeriod($month, $year),
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            'rows'    => $rows,
            'totals'  => $totals,
            'entries' => $rows,
            'is_nil'  => empty($rows),
        ];
    }
}
