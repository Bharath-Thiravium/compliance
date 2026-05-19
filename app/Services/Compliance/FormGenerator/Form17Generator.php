<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

/**
 * Form17Generator - Health Register Generator
 * 
 * Transforms raw worker data into FORM 17 statutory structure
 * Maps 15 required columns for Health Register
 */
class Form17Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_17';
    protected string $view = 'compliance.forms.form_17';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $records = $rawData['records'] ?? [];

        foreach ($records as $index => $record) {
            $record = $this->normalizeRecord($record);
            
            // Calculate age from date of birth
            $age = '';
            if (!empty($record['date_of_birth'])) {
                try {
                    $age = Carbon::parse($record['date_of_birth'])->age;
                } catch (\Exception $e) {
                    $age = '';
                }
            }

            $rows[] = [
                'sl_no' => $index + 1,
                'works_no' => $record['works_no'] ?? '',
                'name_of_worker' => $record['name_of_worker'] ?? '',
                'sex' => $record['sex'] ?? '',
                'age_last_birthday' => $age,
                'date_of_employment_on_present_work' => $this->formatDate($record['date_of_joining'] ?? null),
                'date_of_leaving_or_transfer' => '',
                'reason_for_leaving_transfer_or_discharge' => '',
                'nature_of_job_or_occupation' => $record['designation'] ?? '',
                'raw_material_or_byproduct_handled' => '',
                'result_of_medical_examination' => '',
                'suspension_period_with_reasons' => '',
                'recertified_fit_to_resume_duty_on' => '',
                'certificate_of_unfitness_or_suspension_issued' => '',
                'signature_with_date_of_certifying_surgeon' => '',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM 17 - Health Register',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'establishment_name' => $tenant['establishment_name'] ?? 'N/A',
                'owner_name' => $tenant['name'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }

    /**
     * Format date to DD-MM-YYYY format
     */
    private function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}
