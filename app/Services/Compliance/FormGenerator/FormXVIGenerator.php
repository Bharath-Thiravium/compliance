<?php

namespace App\Services\Compliance\FormGenerator;

class FormXVIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XVI';
    protected string $view = 'compliance.forms.form_xvi_muster_roll';

    protected function prepareData(array $rawData): array
    {
        $employees = [];

        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $empCode = $record['employee_code'] ?? '';

            if (!isset($employees[$empCode])) {
                $employees[$empCode] = [
                    'name'        => $record['name']        ?? '',
                    'father_name' => $record['father_name'] ?? '',
                    'sex'         => $record['sex']         ?? '',
                    'designation' => $record['designation'] ?? '',
                    'remarks'     => '',
                ];
                for ($i = 1; $i <= 31; $i++) {
                    $employees[$empCode]["day_$i"] = '';
                }
            }

            $date = $record['attendance_date'] ?? '';
            if ($date) {
                $day = (int)date('d', strtotime($date));
                if ($day >= 1 && $day <= 31) {
                    $employees[$empCode]["day_$day"] = $record['status'] ?? '';
                }
            }
        }

        return [
            'header' => [
                'form_title' => 'FORM XVI - Muster Roll (CLRA)',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => array_values($employees),
            'is_nil' => count($employees) === 0,
        ];
    }
}
