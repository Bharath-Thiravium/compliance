<?php

namespace App\Services\Compliance\FormGenerator;

class EPFInspectionGenerator extends BaseFormGenerator
{
    protected string $formCode = 'EPF_INSPECTION';
    protected string $view     = 'compliance.forms.epf_inspection';

    protected function prepareData(array $rawData): array
    {
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'inspection_date' => $record['inspection_date'] ?? '',
                'authority'       => $record['authority']       ?? '',
                'reference'       => $record['reference']       ?? '',
                'remarks'         => $record['remarks']         ?? '',
            ];
        }

        return [
            'header' => [
                'form_title'         => 'EPF Inspection Register',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'pf_code'            => $branch['pf_code']            ?? $tenant['pf_code'] ?? '',
                'factory_name'       => $branch['name']               ?? '',
                'address'            => $branch['address']            ?? '',
                'owner_name'         => $tenant['owner_name']         ?? $tenant['name'] ?? '',
                'period'             => $this->formatPeriod($month, $year),
                'branch'             => $branch,
                'tenant'             => $tenant,
            ],
            'rows'   => $rows,
            'is_nil' => empty($rows),
        ];
    }
}
