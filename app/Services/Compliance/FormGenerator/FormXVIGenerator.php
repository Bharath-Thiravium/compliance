<?php

namespace App\Services\Compliance\FormGenerator;

class FormXVIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XVI';
    protected string $view = 'compliance.forms.form_xvi';

    // Status abbreviation map for muster roll day cells
    private function statusCode(string $status): string
    {
        return match(strtolower(trim($status))) {
            'present'                  => 'P',
            'absent'                   => 'A',
            'leave', 'paid leave'      => 'L',
            'holiday', 'public holiday'=> 'H',
            'week off', 'weekoff', 'wo'=> 'WO',
            'half day'                 => 'HD',
            default                    => strtoupper(substr(trim($status), 0, 2)),
        };
    }

    protected function prepareData(array $rawData): array
    {
        $employees = [];

        foreach ($rawData['records'] ?? [] as $record) {
            $record  = $this->normalizeRecord($record);
            $empCode = $record['employee_code'] ?? '';

            if (!isset($employees[$empCode])) {
                $gender = strtolower(trim($record['sex'] ?? $record['gender'] ?? ''));
                $employees[$empCode] = [
                    'name'        => $record['name']        ?? '',
                    'father_name' => $record['father_name'] ?? '',
                    'sex'         => in_array($gender, ['female', 'f']) ? 'F' : (in_array($gender, ['male', 'm']) ? 'M' : ''),
                    'designation' => $record['designation'] ?? '',
                    'remarks'     => '',
                ];
                for ($i = 1; $i <= 31; $i++) {
                    $employees[$empCode]["day_$i"] = '';
                }
            }

            $date = $record['attendance_date'] ?? '';
            if ($date) {
                $day = (int) date('d', strtotime($date));
                if ($day >= 1 && $day <= 31) {
                    $employees[$empCode]["day_$day"] = $this->statusCode($record['status'] ?? '');
                }
            }
        }

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');
        $period = $this->formatPeriod($month, $year);

        $contractorName    = $tenant['name']    ?? '';
        $establishmentName = $branch['name']    ?? '';
        $principalEmployer = $tenant['name']    ?? '';
        $workNature        = $branch['address'] ?? $branch['name'] ?? '';
        $workLocation      = $branch['address'] ?? $branch['name'] ?? '';

        return [
            'header' => [
                'form_title'         => 'FORM XVI - Muster Roll (CLRA)',
                'period'             => $period,
                'branch'             => $branch,
                'tenant'             => $tenant,
                'contractor_name'    => $contractorName,
                'establishment_name' => $establishmentName,
                'principal_employer' => $principalEmployer,
                'work_nature'        => $workNature,
                'work_location'      => $workLocation,
                'wage_period'        => $period,
            ],
            // Root-level vars the Blade reads directly
            'contractor_name'    => $contractorName,
            'establishment_name' => $establishmentName,
            'principal_employer' => $principalEmployer,
            'work_nature'        => $workNature,
            'work_location'      => $workLocation,
            'wage_period'        => $period,
            'rows'               => array_values($employees),
            'is_nil'             => count($employees) === 0,
        ];
    }
}
