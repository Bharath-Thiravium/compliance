<?php

namespace App\Services\Compliance\FormGenerator;

class ESIForm12Generator extends BaseFormGenerator
{
    protected string $formCode = 'ESI_FORM_12';
    protected string $view = 'compliance.forms.esi_form_12';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            
            $rows[] = [
                'employer_name'       => trim($tenant['name'] ?? ''),
                'code_no'             => trim($branch['esi_code'] ?? ''),
                'branch_office'       => trim($branch['name'] ?? ''),
                'industry_nature'     => '',

                'insured_name'        => trim($record['employee_name'] ?? ''),
                'insurance_no'        => trim($record['insurance_no'] ?? ''),
                'sex'                 => trim($record['gender'] ?? ''),
                'age'                 => trim($record['age'] ?? ''),
                'occupation'          => trim($record['occupation'] ?? ''),

                'accident_address'    => trim($branch['address'] ?? ''),
                'department'          => trim($record['department'] ?? ''),
                'shift_hour'          => trim($record['shift_hour'] ?? ''),

                'exact_place'         => trim($record['exact_place'] ?? ''),

                'injury_nature'       => trim($record['severity'] ?? $record['injury_nature'] ?? ''),
                'injury_location'     => trim($record['injury_location'] ?? ''),
                'hospital_info'       => trim($record['hospital_info'] ?? ''),

                'accident_description' => trim($record['description'] ?? ''),

                'death'               => $record['death'] ?? 'no',
                'death_date'          => trim($record['death_date'] ?? ''),

                'wages_payable'       => $record['wages_payable'] ?? 'no',
                'contravention'       => $record['contravention'] ?? 'no',

                'witness_1'           => trim($record['witness_1'] ?? ''),
                'witness_2'           => trim($record['witness_2'] ?? ''),

                'machine_involved'    => trim($record['machine_involved'] ?? ''),
                'machinery_fenced'    => $record['machinery_fenced'] ?? 'no',

                'person_doing'        => trim($record['person_doing'] ?? ''),

                'employer_vehicle'    => $record['employer_vehicle'] ?? 'no',
                'employer_permission' => $record['employer_permission'] ?? 'no',
                'transport_operated'  => $record['transport_operated'] ?? 'no',

                'despatch_date'       => trim($record['despatch_date'] ?? ''),

                'designation'         => trim($record['designation'] ?? ''),
                'diary_no'            => trim($record['diary_no'] ?? ''),
                'branch_manager'      => trim($record['branch_manager'] ?? ''),
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'ESI FORM 12 - Accident Report',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? '') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? '',
                'esi_code' => $branch['esi_code'] ?? '',
                'factory_name' => $branch['name'] ?? '',
                'address' => $branch['address'] ?? '',
                'owner_name' => $tenant['name'] ?? '',
                'place' => $branch['address'] ?? '',
                'district' => $branch['district'] ?? '',
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
