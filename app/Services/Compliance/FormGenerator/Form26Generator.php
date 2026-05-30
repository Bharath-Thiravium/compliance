<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Form26Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_26';
    protected string $view     = 'compliance.forms.form_26';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? 2024;
        $tenant  = $rawData['tenant'] ?? [];
        $branch  = $rawData['branch'] ?? [];

        Log::info('FORM_26 prepareData', ['raw_count' => count($records)]);

        $rows = [];
        $seen = [];
        $slNo = 1;

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);

            // Composite deduplication: incident_id is strongest; fall back to date+employee+location
            $key = $record['id'] ?? implode('|', [
                $record['incident_date']  ?? '',
                $record['employee_name']  ?? $record['employee_code'] ?? '',
                $record['location']       ?? '',
            ]);

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $rows[] = [
                'running_sl_no'                                       => (string) $slNo++,
                'date_and_hour_of_accident'                           => $this->formatDate($record['incident_date'] ?? null),
                'name_and_designation_of_person_injured'              => $this->formatNameDesignation($record['employee_name'] ?? '', $record['designation'] ?? ''),
                'exact_place_of_accident'                             => $record['location']          ?? '',
                'full_description_of_accident'                        => $record['description']       ?? '',
                'nature_extent_location_of_injury'                    => '',
                'date_of_despatch_of_report_form_18'                  => '',
                'date_of_return_to_work'                              => '',
                'date_of_despatch_of_return_to_work_report'           => '',
                'date_of_despatch_of_subsequent_reports_form_18b'     => '',
                'number_of_days_away_from_work'                       => '',
                'number_of_man_days_lost'                             => '',
                'details_of_disablement_and_loss_of_earning_capacity' => '',
                'remarks_and_initials_of_manager'                     => '',
            ];
        }

        Log::info('FORM_26 final rows', ['count' => count($rows)]);

        return [
            'header' => [
                'form_title'          => 'FORM 26 - Register of Accidents',
                'factory_name'        => $branch['name']    ?? '',
                'factory_address'     => $branch['address'] ?? '',
                'calendar_year'       => (string) $year,
                'registration_number' => $branch['registration_number'] ?? '',
                'period'              => $this->formatPeriod($month, $year),
                'branch'              => $branch,
                'tenant'              => is_array($tenant) ? ($tenant['name'] ?? '') : $tenant,
            ],
            'rows'   => $rows,
            'totals' => [],
            'is_nil' => empty($rows),
        ];
    }

    private function formatDate(?string $date): string
    {
        if (!$date) return '';
        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    private function formatNameDesignation(string $name, string $designation): string
    {
        return implode(' / ', array_filter([$name, $designation]));
    }
}
