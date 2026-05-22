<?php

namespace App\Services\Compliance\FormGenerator;

class InspectionBasedFormGenerator extends BaseFormGenerator
{
    protected string $formCode;
    protected string $view;
    
    private array $formTitles = [
        'FORM_7' => 'FORM 7 - Notice of Periods for Adult Workers',
        'HAZARD_REG' => 'Hazardous Process Register',
        'EPF_INSPECTION' => 'EPF Inspection Register',
        'SHOPS_FORM_13' => 'SHOPS FORM 13 - Attendance Register',
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
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'inspection_date' => $record->inspection_date ?? null,
                'authority' => $record->authority ?? 'N/A',
                'reference' => $record->reference ?? 'N/A',
                'remarks' => $record->remarks ?? 'N/A',
            ];
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
}
