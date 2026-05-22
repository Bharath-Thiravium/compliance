<?php

namespace App\Services\Compliance\FormGenerator;

class FormAGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_A';
    protected string $view     = 'compliance.forms.form_a';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $tenant  = $rawData['tenant']  ?? [];
        $branch  = $rawData['branch']  ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);

            // Derive surname from name (last word) if not stored separately
            $fullName = $record['employee_name'] ?? $record['name'] ?? '';
            $nameParts = explode(' ', trim($fullName));
            $surname = count($nameParts) > 1 ? end($nameParts) : '';

            // skill_type maps to category (HS/SC/ST/US)
            $skillMap = [
                'highly_skilled' => 'HS', 'highly skilled' => 'HS', 'hs' => 'HS',
                'skilled'        => 'SC', 'sc'             => 'SC',
                'semi_skilled'   => 'SS', 'semi skilled'   => 'SS', 'ss' => 'SS',
                'unskilled'      => 'US', 'un skilled'     => 'US', 'us' => 'US',
            ];
            $rawSkill = strtolower(trim($record['skill_type'] ?? ''));
            $category = $skillMap[$rawSkill] ?? ($record['skill_type'] ?? '');

            // Bank branch display: "Bank Name (IFSC)"
            $bankName = $record['bank_name'] ?? '';
            $ifsc     = $record['ifsc']      ?? '';
            $branchIfsc = trim(implode(' ', array_filter([
                $bankName,
                $ifsc ? "({$ifsc})" : '',
            ])));

            $rows[] = [
                'employee_code'      => $record['employee_code']     ?? '',
                'employee_name'      => $fullName,
                'surname'            => $surname,
                'father_name'        => $record['father_name']        ?? '',
                'gender'             => $record['gender']             ?? '',
                'date_of_birth'      => $record['date_of_birth']      ?? '',
                'marital_status'     => $record['marital_status']     ?? '',
                'nationality'        => $record['nationality']        ?? 'Indian',
                'education_level'    => $record['education_level']    ?? '',
                'mobile'             => $record['mobile']             ?? '',
                'email'              => $record['email']              ?? '',
                'permanent_address'  => $record['permanent_address']  ?? '',
                'present_address'    => $record['present_address']    ?? $record['permanent_address'] ?? '',
                'designation'        => $record['designation']        ?? '',
                'department'         => $record['department']         ?? '',
                'skill_type'         => $record['skill_type']         ?? '',
                'category'           => $category,
                'employment_type'    => $record['employment_type']    ?? 'Permanent',
                'date_of_joining'    => $record['date_of_joining']    ?? '',
                'date_of_exit'       => $record['date_of_exit']       ?? '',
                'reason_for_exit'    => $record['reason_for_exit']    ?? '',
                'pf_number'          => $record['pf_number']          ?? '',
                'esi_number'         => $record['esi_number']         ?? '',
                'uan_number'         => $record['uan_number']         ?? $record['pf_number'] ?? '',
                'pan'                => $record['pan']                ?? '',
                'aadhaar'            => $record['aadhaar']            ?? '',
                'bank_account'       => $record['bank_account']       ?? '',
                'bank_name'          => $bankName,
                'ifsc'               => $ifsc,
                'branch_ifsc'        => $branchIfsc,
                'basic_salary'       => (float) ($record['basic_salary'] ?? 0),
                'status'             => $record['status']             ?? 'active',
                'identification_mark'=> $record['identification_mark'] ?? $record['marks_of_identification'] ?? '',
            ];
        }

        return [
            'header' => [
                'form_title'         => 'FORM A - Register of Employees',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'branch_name'        => $branch['name']               ?? '',
                'period'             => $this->formatPeriod($month, $year),
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            'rows'   => $rows,
            'totals' => [],
            'is_nil' => empty($rows),
        ];
    }
}
