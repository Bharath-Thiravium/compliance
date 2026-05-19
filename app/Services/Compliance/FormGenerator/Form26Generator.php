<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class Form26Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_26';
    protected string $view = 'compliance.forms.form_26';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $slNo = 1;

        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            
            $rows[] = [
                'running_sl_no' => (string)$slNo,
                'date_and_hour_of_accident' => $this->formatDate($record['incident_date'] ?? null),
                'name_and_designation_of_person_injured' => $this->formatNameAndDesignation(
                    $record['employee_name'] ?? '',
                    $record['designation'] ?? ''
                ),
                'exact_place_of_accident' => $record['location'] ?? '',
                'full_description_of_accident' => $record['description'] ?? '',
                'nature_extent_location_of_injury' => '',
                'date_of_despatch_of_report_form_18' => '',
                'date_of_return_to_work' => '',
                'date_of_despatch_of_return_to_work_report' => '',
                'date_of_despatch_of_subsequent_reports_form_18b' => '',
                'number_of_days_away_from_work' => '',
                'number_of_man_days_lost' => '',
                'details_of_disablement_and_loss_of_earning_capacity' => '',
                'remarks_and_initials_of_manager' => '',
            ];
            
            $slNo++;
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM 26 - Register of Accidents',
                'factory_name' => $branch['name'] ?? '',
                'factory_address' => $branch['address'] ?? '',
                'calendar_year' => (string)$year,
                'registration_number' => $branch['registration_number'] ?? '',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? '') : $tenant,
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }

    /**
     * Format date to DD-MM-YYYY
     */
    private function formatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }
        
        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Format name and designation together
     */
    private function formatNameAndDesignation(string $name, string $designation): string
    {
        $parts = array_filter([$name, $designation]);
        return implode(' / ', $parts);
    }
}
