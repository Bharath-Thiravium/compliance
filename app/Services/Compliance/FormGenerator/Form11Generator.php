<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\Log;

class Form11Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_11';
    protected string $view     = 'compliance.forms.form_11';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];

        Log::info('FORM_11 prepareData', ['raw_count' => count($records)]);

        $rows = [];
        $seen = [];

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);

            // Composite deduplication: incident_id is the strongest key; fall back to date+type+person
            $key = $record['id'] ?? implode('|', [
                $record['incident_date'] ?? '',
                $record['injury_type']   ?? '',
                $record['name']          ?? '',
            ]);

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $rows[] = [
                'date_of_notice'   => $this->formatDate($record['notice_date']    ?? null),
                'time_of_notice'   => $record['notice_time']   ?? '',
                'injured_person'   => trim(($record['name'] ?? '') . (!empty($record['address']) ? ', ' . $record['address'] : '')),
                'sex'              => $this->mapGender($record['gender']          ?? ''),
                'age'              => $record['age']            ?? '',
                'insurance_no'     => $record['esi_number']     ?? '',
                'occupation'       => $record['designation']    ?? '',
                'cause'            => $record['cause']          ?? '',
                'nature'           => $record['injury_type']    ?? '',
                'injury_date'      => $this->formatDate($record['incident_date']  ?? null),
                'injury_time'      => $record['incident_time']  ?? '',
                'place'            => $record['location']       ?? '',
                'activity'         => $record['activity']       ?? '',
                'first_aid_person' => $record['first_aid_by']   ?? '',
                'signature'        => '',
                'witnesses'        => $record['witness']        ?? '',
                'remarks'          => $record['remarks']        ?? '',
            ];
        }

        Log::info('FORM_11 final rows', ['count' => count($rows)]);

        return [
            'header' => [
                'company_name'       => $rawData['tenant']['name']    ?? '',
                'contractor_name'    => '',
                'total_workers'      => '',
                'work_location'      => $rawData['branch']['address'] ?? '',
                'principal_employer' => $rawData['tenant']['name']    ?? '',
                'month_year'         => $rawData['period']            ?? '',
                'tenant'             => $rawData['tenant']            ?? [],
                'branch'             => $rawData['branch']            ?? [],
            ],
            'rows'   => $rows,
            'totals' => [],
            'is_nil' => empty($rows),
        ];
    }

    private function formatDate($date): string
    {
        if (!$date) return '';
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    private function mapGender($gender): string
    {
        return ['M' => 'M', 'F' => 'F', 'Male' => 'M', 'Female' => 'F'][$gender] ?? '';
    }
}
