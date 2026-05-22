<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class FormXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIII';
    protected string $view = 'compliance.forms.form_xiii_register_of_workmen';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'name' => $record['name'] ?? null,
                'age' => $this->calculateAge($record['date_of_birth'] ?? null),
                'sex' => $record['gender'] ?? null,
                'father_name' => $record['father_name'] ?? null,
                'designation' => $record['designation'] ?? null,
                'permanent_address' => $record['permanent_address'] ?? null,
                'local_address' => $record['local_address'] ?? null,
                'joining_date' => $this->formatDate($record['joining_date'] ?? null),
                'termination_date' => $this->formatDate($record['termination_date'] ?? null),
                'termination_reason' => null,
                'remarks' => null,
            ];
        }

        return [
            'header' => [
                'form_title' => 'FORM XIII - Register of Workmen Employed by Contractor',
                'period' => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'contractor_name' => $rawData['tenant']['establishment_name'] ?? 'NIL',
            'establishment_name' => $rawData['branch']['name'] ?? 'NIL',
            'work_nature' => 'Contract Labour',
            'work_location' => $rawData['branch']['address'] ?? 'NIL',
            'principal_employer' => $rawData['tenant']['name'] ?? 'NIL',
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }

    private function calculateAge(?string $dateOfBirth): ?string
    {
        if (!$dateOfBirth) {
            return null;
        }

        try {
            $dob = Carbon::parse($dateOfBirth);
            return (string) $dob->diffInYears(Carbon::now());
        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return null;
        }
    }
}
