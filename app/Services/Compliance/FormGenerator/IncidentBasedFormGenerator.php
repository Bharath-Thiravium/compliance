<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class IncidentBasedFormGenerator extends BaseFormGenerator
{
    protected string $formCode;
    protected string $view;

    private array $formTitles = [
        'FORM_8' => 'FORM 8 - Register of Accidents',
        'FORM_11' => 'FORM 11 - Accident Register',
        'FORM_26' => 'FORM 26 - Notice of Accident',
        'FORM_26A' => 'FORM 26A - Notice of Dangerous Occurrence',
        'ESI_FORM_12' => 'ESI FORM 12 - Accident Register',
        'FORM_18' => 'FORM 18 - Register of Child Workers',
    ];

    public function __construct(string $formCode)
    {
        $this->formCode = $formCode;
        $this->view = 'compliance.forms.' . strtolower($formCode);
        parent::__construct();
    }

    protected function prepareData(array $rawData): array
    {
        $rows = [];

        if ($this->formCode === 'FORM_18') {
            $rows = $this->prepareForm18Data($rawData);
        } else {
            foreach ($rawData['records'] as $record) {
                $rows[] = [
                    'employee_name' => $record->employee_name ?? 'N/A',
                    'esi_number' => $record->esi_number ?? 'N/A',
                    'incident_date' => $record->incident_date ?? null,
                    'incident_type' => $record->incident_type ?? 'N/A',
                    'location' => $record->location ?? 'N/A',
                    'description' => $record->description ?? 'N/A',
                ];
            }
        }

        return [
            'header' => [
                'form_title' => $this->formTitles[$this->formCode] ?? $this->formCode,
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }

    private function prepareForm18Data(array $rawData): array
    {
        $rows = [];

        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'employee_code' => $record->employee_code ?? 'N/A',
                'employee_name' => $record->employee_name ?? 'N/A',
                'designation' => $record->designation ?? 'N/A',
                'date_of_joining' => $record->date_of_joining ?? 'N/A',
            ];
        }

        return $rows;
    }
}
