<?php

namespace App\Services\Compliance\FormGenerator;

class Form12Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_12';
    protected string $view     = 'compliance.forms.form_12';

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
                'employee_code'   => $record['employee_code']  ?? '',
                'employee_name'   => $record['name']           ?? '',
                'father_name'     => $record['father_name']    ?? '',
                'gender'          => $record['gender']         ?? '',
                'date_of_birth'   => $record['date_of_birth']  ?? '',
                'address'         => $record['address']        ?? '',
                'designation'     => $record['designation']    ?? '',
                'department'      => $record['department']     ?? '',
                'date_of_joining' => $record['date_of_joining'] ?? '',
                'date_of_exit'    => $record['date_of_exit']   ?? '',
                'pf_number'       => $record['pf_number']      ?? '',
                'esi_number'      => $record['esi_number']     ?? '',
                'uan'             => $record['uan']            ?? '',
                'mobile'          => $record['mobile']         ?? '',
                'bank_account'    => $record['bank_account']   ?? '',
                'bank_name'       => $record['bank_name']      ?? '',
                'ifsc'            => $record['ifsc']           ?? '',
                'skill_type'      => $record['skill_type']     ?? '',
                'nationality'     => $record['nationality']    ?? '',
                'group'           => '',
                'relay'           => '',
                'certificate_no'  => '',
                'token_no'        => '',
                'remarks'         => '',
            ];
        }

        return [
            'header' => [
                'form_title'         => 'FORM 12 - Register of Adult Workers',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'branch_name'        => $branch['name']               ?? '',
                'period'             => $this->formatPeriod($month, $year),
                'branch'             => $branch,
                'tenant'             => $tenant,
            ],
            'rows'   => $rows,
            'totals' => [],
            'is_nil' => empty($rows),
        ];
    }
}
