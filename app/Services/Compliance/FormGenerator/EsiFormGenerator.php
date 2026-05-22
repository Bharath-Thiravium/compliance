<?php

namespace App\Services\Compliance\FormGenerator;

class EsiFormGenerator extends BaseFormGenerator
{
    protected string $formCode = 'ESI_FORM_12';
    protected string $view = 'compliance.forms.esi_form_12';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'employee_name' => $record->name ?? 'N/A',
                'incident_date' => $record->incident_date ?? 'N/A',
                'incident_type' => $record->incident_type ?? 'N/A',
                'location' => $record->location ?? 'N/A',
                'description' => $record->description ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'ESI FORM 12 - Accident Report',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
