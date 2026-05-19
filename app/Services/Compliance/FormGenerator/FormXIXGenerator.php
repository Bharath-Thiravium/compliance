<?php

namespace App\Services\Compliance\FormGenerator;

class FormXIXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIX';
    protected string $view     = 'compliance.forms.form_xix';

    protected function prepareData(array $rawData): array
    {
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record     = $this->normalizeRecord($record);
            $gross      = (float) ($record['gross_salary']    ?? 0);
            $pf         = (float) ($record['pf_employee']     ?? 0);
            $esi        = (float) ($record['esi_employee']    ?? 0);
            $pt         = (float) ($record['professional_tax'] ?? 0);
            $otherDeduct = (float) ($record['other_deductions'] ?? 0);
            $totalDeduct = $pf + $esi + $pt + $otherDeduct;

            $rows[] = [
                'employee_code'    => $record['employee_code']   ?? '',
                'contractor_name'  => $tenant['name']            ?? '',
                'workman_name'     => $record['name']            ?? '',
                'father_name'      => $record['father_name']     ?? '',
                'designation'      => $record['designation']     ?? '',
                'uan'              => $record['uan']             ?? $record['pf_number'] ?? '',
                'esi_number'       => $record['esi_number']      ?? '',
                'work_nature'      => $record['work_nature']     ?? 'Manufacturing',
                'work_location'    => $branch['address']         ?? $branch['name'] ?? '',
                'period_ending'    => $this->formatPeriod($month, $year),
                'days_worked'      => $record['days_worked']     ?? $record['total_days_worked'] ?? 0,
                'daily_rate'       => (float) ($record['daily_rate']    ?? 0),
                'basic_wages'      => (float) ($record['basic_earned']  ?? 0),
                'da'               => (float) ($record['da_earned']     ?? 0),
                'hra'              => (float) ($record['hra_earned']    ?? 0),
                'other_allowances' => (float) ($record['other_allowances'] ?? 0),
                'overtime_wages'   => (float) ($record['overtime_wages'] ?? 0),
                'gross_salary'     => $gross,
                'pf'               => $pf,
                'esi'              => $esi,
                'pt'               => $pt,
                'other_deductions' => $otherDeduct,
                'total_deductions' => (float) ($record['total_deductions'] ?? $totalDeduct),
                'net_salary'       => (float) ($record['net_salary']     ?? ($gross - $totalDeduct)),
                'payment_date'     => $record['payment_date']    ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'days_worked', 'basic_wages', 'da', 'hra', 'other_allowances',
            'overtime_wages', 'gross_salary', 'pf', 'esi', 'pt',
            'other_deductions', 'total_deductions', 'net_salary',
        ]);

        return [
            'header' => [
                'form_title'      => 'FORM XIX - Wage Slip (CLRA)',
                'period'          => $this->formatPeriod($month, $year),
                'contractor_name' => $tenant['name'] ?? '',
                'tenant'          => $tenant,
                'branch'          => $branch,
            ],
            'rows'   => $rows,
            'slips'  => $rows,
            'totals' => $totals,
            'is_nil' => empty($rows),
        ];
    }
}
