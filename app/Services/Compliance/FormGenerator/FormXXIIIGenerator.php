<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXIII';
    protected string $view     = 'compliance.forms.form_xxiii';

    protected function prepareData(array $rawData): array
    {
        $rows   = [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code'     => $record['employee_code']     ?? '',
                'name'              => $record['employee_name']     ?? $record['name'] ?? '',
                'father_name'       => $record['father_name']       ?? '',
                'sex'               => $record['sex']               ?? $record['gender'] ?? '',
                'designation'       => $record['designation']       ?? '',
                'overtime_dates'    => $record['overtime_dates']    ?? '',
                'total_overtime'    => (float) ($record['total_overtime']    ?? $record['overtime_hours'] ?? 0),
                'normal_rate'       => (float) ($record['normal_rate']       ?? $record['basic_earned']   ?? 0),
                'overtime_rate'     => (float) ($record['overtime_rate']     ?? 0),
                'overtime_earnings' => (float) ($record['overtime_earnings'] ?? $record['overtime_wages'] ?? 0),
                'payment_date'      => $record['payment_date']      ?? '',
                'remarks'           => $record['remarks']           ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, ['total_overtime', 'overtime_earnings']);

        return [
            'header' => [
                'contractor_name'    => $tenant['name']    ?? '',
                'work_location'      => $branch['address'] ?? '',
                'establishment_name' => $branch['name']    ?? '',
                'principal_employer' => $tenant['name']    ?? '',
                'month_year'         => $this->formatPeriod($month, $year),
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            'rows'   => $rows,
            'totals' => $totals,
            'is_nil' => empty($rows),
        ];
    }
}
