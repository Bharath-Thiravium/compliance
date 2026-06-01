<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class FormXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIII';
    protected string $view = 'compliance.forms.form_xiii';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $contractorName = null;
        
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            
            if (!$contractorName && isset($record['contractor_name']) && $record['contractor_name'] !== 'N/A') {
                $contractorName = $record['contractor_name'];
            }
            
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

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        return [
            'header' => [
                'form_title' => 'FORM XIII - Register of Workmen Employed by Contractor',
                'period' => $this->formatPeriod($month, $year),
                'contractor_name' => $contractorName ?? 'NIL',
                'tenant' => [
                    'name' => $tenant['name'] ?? 'NIL',
                    'address' => $tenant['address'] ?? '',
                ],
                'branch' => [
                    'name' => $branch['name'] ?? 'NIL',
                    'address' => $branch['address'] ?? 'NIL',
                ],
            ],
            'rows' => $rows,
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
