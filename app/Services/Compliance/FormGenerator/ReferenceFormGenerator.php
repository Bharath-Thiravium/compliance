<?php

namespace App\Services\Compliance\FormGenerator;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Reference-based Form Generator
 * 
 * Uses reference templates that strictly follow official government PDF structures
 * Templates located in: resources/views/compliance/forms/reference/
 */
class ReferenceFormGenerator extends BaseFormGenerator
{
    protected array $referenceTemplateMap = [
        'FORM_B' => 'compliance.forms.reference.form_b_reference',
        'FORM_XIII' => 'compliance.forms.reference.form_xiii_reference',
        'ESI_FORM_12' => 'compliance.forms.reference.esi_form_12_reference',
        'EPF_INSPECTION' => 'compliance.forms.reference.epf_inspection_reference',
    ];

    public function __construct(string $formCode)
    {
        $this->formCode = $formCode;
        $this->view = $this->referenceTemplateMap[$formCode] ?? null;
        parent::__construct();
    }

    public function supportsForm(string $formCode): bool
    {
        return isset($this->referenceTemplateMap[$formCode]);
    }

    protected function prepareData(array $rawData): array
    {
        $header = [
            'tenant' => $rawData['tenant'] ?? [],
            'branch' => $rawData['branch'] ?? [],
            'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
            'form_title' => $this->getFormTitle(),
        ];

        $rows = $this->transformRecords($rawData['records']);
        $is_nil = empty($rows);
        
        $totals = [];
        if (!$is_nil && $this->hasTotals()) {
            $totals = $this->calculateTotalsForForm($rows);
        }

        return [
            'header' => $header,
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => $is_nil,
        ];
    }

    protected function transformRecords($records): array
    {
        $rows = [];
        
        foreach ($records as $record) {
            $rows[] = $this->transformRecord($record);
        }
        
        return $rows;
    }

    protected function transformRecord($record): array
    {
        return (array) $record;
    }

    protected function hasTotals(): bool
    {
        return in_array($this->formCode, ['FORM_B', 'FORM_XVI', 'FORM_XVII']);
    }

    protected function calculateTotalsForForm(array $rows): array
    {
        $totalsConfig = [
            'FORM_B' => [
                'basic_earned', 'da_earned', 'hra_earned', 'overtime_wages',
                'gross_salary', 'pf_employee', 'esi_employee', 'total_deductions', 'net_salary'
            ],
        ];

        $fields = $totalsConfig[$this->formCode] ?? [];
        return $this->calculateTotals($rows, $fields);
    }

    protected function getFormTitle(): string
    {
        $titles = [
            'FORM_B' => 'FORM B - Register of Wages',
            'FORM_XIII' => 'FORM XIII - Register of Contract Labour',
            'ESI_FORM_12' => 'FORM 12 - Accident Book',
            'EPF_INSPECTION' => 'Inspection Register',
        ];

        return $titles[$this->formCode] ?? $this->formCode;
    }
}
