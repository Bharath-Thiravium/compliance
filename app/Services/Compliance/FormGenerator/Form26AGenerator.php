<?php

namespace App\Services\Compliance\FormGenerator;

class Form26AGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_26A';
    protected string $view = 'compliance.forms.form_26a';

    protected function prepareData(array $rawData): array
    {
        $year = $rawData['meta']['year'] ?? date('Y');
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $date = $record['incident_date'] ?? null;
            $time = $record['incident_time'] ?? null;
            $rows[] = [
                'calendar_year'  => $year,
                'date_and_hour'  => $date ? ($time ? $date . ' ' . $time : $date) : '',
                'report_date'    => '',
                'place'          => $record['location'] ?? 'N/A',
                'description'    => $record['description'] ?? 'N/A',
                'damage_details' => $record['cause'] ?? '',
                'remarks'        => $record['remarks'] ?? '',
            ];
        }

        $month  = $rawData['meta']['month'] ?? 1;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title'          => 'FORM 26A - Register of Dangerous Occurrences',
                'period'              => $this->formatPeriod($month, $year),
                'branch'              => $branch,
                'tenant'              => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details'      => $tenant,
                'factory_name'        => $branch['name'] ?? 'N/A',
                'factory_address'     => $branch['address'] ?? 'N/A',
                'address'             => $branch['address'] ?? 'N/A',
                'registration_number' => $tenant['factory_license_no'] ?? '',
                'establishment_name'  => $tenant['establishment_name'] ?? $tenant['name'] ?? 'N/A',
                'owner_name'          => $tenant['owner_name'] ?? $tenant['name'] ?? 'N/A',
                'district'            => $branch['district'] ?? 'N/A',
            ],
            'rows'   => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
