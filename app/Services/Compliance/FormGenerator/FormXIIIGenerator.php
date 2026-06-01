<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FormXIIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIII';
    protected string $view = 'compliance.forms.form_xiii';

    protected function prepareData(array $rawData): array
    {
        Log::info('FORM XIII GENERATOR: START', [
            'record_count' => count($rawData['records'] ?? []),
        ]);

        $rows = [];
        $contractorName = null;
        $contractorAddress = null;
        
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            
            // Extract contractor details from first record
            if (!$contractorName && isset($record['contractor_name'])) {
                $contractorName = $record['contractor_name'];
                $contractorAddress = $record['contractor_address'] ?? '';
                
                Log::info('FORM XIII GENERATOR: Contractor extracted', [
                    'contractor_name' => $contractorName,
                    'contractor_address' => $contractorAddress,
                ]);
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

        Log::info('FORM XIII GENERATOR: Rows transformed', [
            'total_rows' => count($rows),
            'contractor_name' => $contractorName,
        ]);

        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;

        $result = [
            'header' => [
                'form_title' => 'FORM XIII - Register of Workmen Employed by Contractor',
                'period' => $this->formatPeriod($month, $year),
                'contractor_name' => $contractorName ?? '',
                'contractor_address' => $contractorAddress ?? '',
                'tenant' => [
                    'name' => $tenant['name'] ?? 'N/A',
                    'address' => $tenant['address'] ?? '',
                ],
                'branch' => [
                    'name' => $branch['name'] ?? 'N/A',
                    'address' => $branch['address'] ?? 'N/A',
                ],
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];

        Log::info('FORM XIII GENERATOR: COMPLETE', [
            'is_nil' => $result['is_nil'],
            'row_count' => count($rows),
        ]);

        return $result;
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
