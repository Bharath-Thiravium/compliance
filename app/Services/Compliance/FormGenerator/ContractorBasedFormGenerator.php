<?php

namespace App\Services\Compliance\FormGenerator;

class ContractorBasedFormGenerator extends BaseFormGenerator
{
    protected string $formCode;
    protected string $view;

    private array $formTitles = [
        'FORM_XIII' => 'FORM XIII - Register of Contract Labour',
        'FORM_XIV' => 'FORM XIV - Register of Workmen',
        'FORM_XII' => 'FORM XII - Register of Contractors',
        'CLRA_LICENSE' => 'License Register',
        'FORM_XXIV' => 'FORM XXIV - Annual Return',
        'FORM_XXV' => 'FORM XXV - Half-Yearly Return',
        'FORM_XX' => 'FORM_XX-Register of Deductions for Damage or Loss',
        'SHOPS_FORM_1' => 'SHOPS FORM 1 - Register of Employment',
    ];

    public function __construct(string $formCode)
    {
        $this->formCode = $formCode;
        $this->view = 'compliance.forms.' . strtolower($formCode);
        parent::__construct();
    }

    protected function prepareData(array $rawData): array
    {
        if ($this->formCode === 'FORM_XX') {
            return $this->prepareFormXX($rawData);
        }

        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'worker_name' => $record->worker_name ?? 'N/A',
                'contractor_name' => $record->contractor_name ?? 'N/A',
                'deployment_start' => $record->deployment_start ?? null,
                'deployment_end' => $record->deployment_end ?? null,
                'wage_rate' => $record->wage_rate ?? 0,
                'work_order' => $record->work_order ?? 'N/A',
            ];
        }

        $totals = $this->calculateTotals($rows, ['wage_rate']);

        return [
            'header' => [
                'form_title' => $this->formTitles[$this->formCode] ?? $this->formCode,
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }

    private function prepareFormXX(array $rawData): array
    {
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $contractorName = $rawData['contractor_name'] ?? 'N/A';

        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'employee_name' => $record->name ?? '',
                'father_name' => '',
                'designation' => $record->designation ?? '',
                'damage_particulars' => 'Deduction from salary',
                'damage_date' => $record->period_from ?? '',
                'showed_cause' => '',
                'witness_name' => '',
                'deduction_amount' => ($record->fines ?? 0) + ($record->other_deductions ?? 0),
                'instalments' => '',
                'first_month' => '',
                'last_month' => '',
                'remarks' => '',
            ];
        }

        $workNature = is_array($branch) ? ($branch['address'] ?? 'N/A') : ($branch->address ?? 'N/A');
        $establishmentName = is_array($branch) ? ($branch['name'] ?? 'N/A') : ($branch->name ?? 'N/A');
        $principalEmployer = is_array($tenant) ? ($tenant['name'] ?? 'N/A') : ($tenant->name ?? 'N/A');

        return [
            'header' => [
                'contractor_name' => $contractorName,
                'work_nature' => $workNature,
                'establishment_name' => $establishmentName,
                'principal_employer' => $principalEmployer,
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'tenant' => $tenant,
                'branch' => $branch
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0
        ];
    }
}
