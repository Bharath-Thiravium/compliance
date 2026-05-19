<?php

namespace App\Services\Compliance\FormGenerator;

class Form25Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_25';
    protected string $view     = 'compliance.forms.form_25';

    protected function prepareData(array $rawData): array
    {
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code'       => $record['employee_code']       ?? '',
                'employee_name'       => $record['name']                ?? '',
                'father_name'         => $record['father_name']         ?? '',
                'designation'         => $record['designation']         ?? '',
                'gender'              => $record['gender']              ?? '',
                'date_of_birth'       => $record['date_of_birth']       ?? '',
                'date_of_joining'     => $record['date_of_joining']     ?? '',
                'place_of_employment' => $record['place_of_employment'] ?? $branch['address'] ?? '',
                'present_days'        => $record['present_days']        ?? 0,
                'absent_days'         => $record['absent_days']         ?? 0,
                'total_days'          => $record['total_days']          ?? 0,
                'date'                => $record['attendance_date']     ?? '',
                'group'               => '',
                'relay'               => '',
                'periods_of_work'     => '',
            ];
        }

        $totals = $this->calculateTotals($rows, ['present_days', 'absent_days', 'total_days']);

        return [
            'header' => [
                'form_title'         => 'FORM 25 - Muster Roll',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'branch_name'        => $branch['name']               ?? '',
                'period'             => $this->formatPeriod($month, $year),
                'branch'             => $branch,
                'tenant'             => $tenant,
            ],
            'rows'   => $rows,
            'totals' => $totals,
            'is_nil' => empty($rows),
        ];
    }
}
