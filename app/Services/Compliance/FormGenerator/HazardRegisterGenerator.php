<?php

namespace App\Services\Compliance\FormGenerator;

class HazardRegisterGenerator extends BaseFormGenerator
{
    protected string $formCode = 'HazardReg';
    protected string $view = 'compliance.forms.hazard_reg';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'sl_no' => count($rows) + 1,
                'hazard_type' => $record['hazard_type'] ?? 'N/A',
                'location' => $record['location'] ?? 'N/A',
                'description' => $record['description'] ?? 'N/A',
                'risk_level' => $record['risk_rating'] ?? $record['risk_level'] ?? 'N/A',
                'control_measures' => $record['control_measure'] ?? $record['control_measures'] ?? 'N/A',
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'Hazardous Process Register',
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
}
