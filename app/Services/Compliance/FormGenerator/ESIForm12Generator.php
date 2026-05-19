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
                'employer_name' => $tenant['name'] ?? 'NIL',
                'code_no' => $branch['esi_code'] ?? 'NIL',
                'branch_office' => $branch['name'] ?? 'NIL',
                'industry_nature' => 'NIL',
                
                'insured_name' => $record['employee_name'] ?? 'NIL',
                'insurance_no' => $record['insurance_no'] ?? 'NIL',
                'sex' => $record['gender'] ?? 'NIL',
                'age' => 'NIL',
                'occupation' => $record['occupation'] ?? 'NIL',
                
                'accident_address' => $branch['address'] ?? 'NIL',
                'department' => $record['department'] ?? 'NIL',
                'shift_hour' => 'NIL',
                
                'exact_place' => 'NIL',
                
                'injury_nature' => $record['severity'] ?? 'NIL',
                'injury_location' => 'NIL',
                'hospital_info' => 'NIL',
                
                'accident_description' => $record['description'] ?? 'NIL',
                
                'death' => 'no',
                'death_date' => 'NIL',
                
                'wages_payable' => 'yes',
                'contravention' => 'no',
                
                'witness_1' => 'NIL',
                'witness_2' => 'NIL',
                
                'machine_involved' => 'NIL',
                'machinery_fenced' => 'no',
                
                'person_doing' => 'NIL',
                
                'employer_vehicle' => 'no',
                'employer_permission' => 'no',
                'transport_operated' => 'no',
                
                'despatch_date' => now()->format('d-m-Y'),
                
                'designation' => 'Manager',
                'diary_no' => 'AUTO',
                'branch_manager' => 'Manager',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'ESI FORM 12 - Accident Report',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'esi_code' => $branch['esi_code'] ?? 'N/A',
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'owner_name' => $tenant['name'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
