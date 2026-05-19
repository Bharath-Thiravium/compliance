<?php

namespace App\Services\Compliance\FormGenerator;

class MasterRegisterFormGenerator extends BaseFormGenerator
{
    protected string $formCode;
    protected string $view;
    
    private array $formTitles = [
        'FORM_12' => 'FORM 12 - Register of Adult Workers',
        'FORM_17' => 'FORM 17 - Register of Young Persons',
        'FORM_2' => 'FORM 2 - Register of Leave',
        'SHOPS_FORM_C' => 'SHOPS FORM C - Bonus Register',
        'SHOPS_FORM_VI' => 'SHOPS FORM VI - Leave Register',
        'CONTRACTOR_MASTER' => 'Contractor Master Register',
        'CLRA_RETURN' => 'CLRA Return - Half-Yearly Return',
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
                'employee_code' => $record->employee_code ?? $record->employee_id ?? 'N/A',
                'name' => $record->name ?? 'N/A',
                'designation' => $record->designation ?? 'N/A',
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
