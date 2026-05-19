<?php

namespace App\Services\Compliance\FormGenerator;

class Form18Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_18';
    protected string $view     = 'compliance.forms.form_18';

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
                'employee_name'   => $record['employee_name']  ?? '',
                'father_name'     => $record['father_name']    ?? '',
                'designation'     => $record['designation']    ?? '',
                'gender'          => $record['gender']         ?? '',
                'age'             => $record['age']            ?? '',
                'esi_number'      => $record['esi_number']     ?? '',
                'address'         => $record['address']        ?? '',
                'date_of_joining' => $record['date_of_joining'] ?? '',
                'incident_date'   => $record['incident_date']  ?? '',
                'notice_date'     => $record['notice_date']    ?? '',
                'location'        => $record['location']       ?? '',
                'cause'           => $record['cause']          ?? '',
                'injury_type'     => $record['injury_type']    ?? '',
                'description'     => $record['description']    ?? '',
                'severity'        => $record['severity']       ?? '',
                'status'          => $record['status']         ?? '',
                'remarks'         => $record['remarks']        ?? '',
            ];
        }

        return [
            'header' => [
                'form_title'         => 'FORM 18 - Report of Accident',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'factory_name'       => $branch['name']               ?? '',
                'address'            => $branch['address']            ?? '',
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
