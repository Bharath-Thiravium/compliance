<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;
use App\Services\Compliance\WageCalculationService;

class PayrollBasedFormGenerator extends BaseFormGenerator
{
    protected string $formCode;
    protected string $view;
    protected WageCalculationService $wageService;
    
    private array $formTitles = [
        'FORM_B' => 'FORM B - Register of Wages',
        'FORM_10' => 'FORM 10 - Overtime Register',
        'FORM_25' => 'FORM 25 - Muster Roll',
        'FORM_XVI' => 'FORM XVI - Register of Wages (CLRA)',
        'FORM_XVII' => 'FORM XVII - Register of Deductions',
        'FORM_XIX' => 'FORM XIX - Muster Roll (CLRA)',
        'FORM_XXIII' => 'FORM XXIII - Register of Overtime',
        'SHOPS_FORM_12' => 'SHOPS FORM 12 - Register of Wages',
        'SHOPS_FINES' => 'Register of Fines',
        'FORM_XXI' => 'FORM XXI - Register of Fines',
        'FORM_XX' => 'FORM XX - Register of Advances',
        'FORM_XXII' => 'FORM XXII - Register of Damage or Loss',
        'SHOPS_UNPAID' => 'Unpaid Wages Register',
    ];

    public function __construct(string $formCode)
    {
        $this->formCode = $formCode;
        $this->view = 'compliance.forms.' . strtolower($formCode);
        $this->wageService = new WageCalculationService();
        parent::__construct();
    }

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = $this->mapRecordToRow($record, $rawData);
        }

        $totals = $this->calculateTotalsForForm($rows);

        $headerData = [
            'form_title' => $this->formTitles[$this->formCode] ?? $this->formCode,
            'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
            'branch' => $rawData['branch'] ?? [],
            'tenant' => $rawData['tenant'] ?? [],
        ];

        if ($this->formCode === 'FORM_10') {
            $headerData['total_workers'] = count($rows);
            $headerData['contractor_name'] = $rawData['contractor_name'] ?? 'N/A';
            $headerData['principal_employer'] = $rawData['principal_employer'] ?? $headerData['tenant']['name'] ?? 'N/A';
        }

        return [
            'header' => $headerData,
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }

    private function mapRecordToRow($record, array $rawData): array
    {
        $employeeCode = $record->employee_code 
            ?? $record->employee_id 
            ?? $record->payroll_employee_code 
            ?? 'EMP-' . ($record->id ?? 'UNKNOWN');

        $row = [
            'employee_code' => $employeeCode,
            'employee_name' => $record->employee_name ?? 'N/A',
            'designation' => $record->designation ?? 'N/A',
            'basic_earned' => $record->basic_earned ?? 0,
            'da_earned' => $record->da_earned ?? 0,
            'hra_earned' => $record->hra_earned ?? 0,
            'overtime_hours' => $record->overtime_hours ?? 0,
            'overtime_wages' => $record->overtime_wages ?? 0,
            'gross_salary' => $record->gross_salary ?? 0,
            'pf_employee' => $record->pf_employee ?? 0,
            'esi_employee' => $record->esi_employee ?? 0,
            'advances' => $record->advances ?? 0,
            'fines' => $record->fines ?? 0,
            'total_deductions' => $record->total_deductions ?? 0,
            'net_salary' => $record->net_salary ?? 0,
            'total_days_worked' => $record->total_days_worked ?? 0,
        ];

        if ($this->formCode === 'FORM_B' && isset($record->employee_id)) {
            $row = $this->enrichFormBData($row, $record, $rawData);
        }

        if ($this->formCode === 'FORM_10' && isset($record->employee_id)) {
            $row = $this->enrichForm10Data($row, $record, $rawData);
        }

        return $row;
    }

    private function calculateTotalsForForm(array $rows): array
    {
        $fields = ['basic_earned', 'da_earned', 'hra_earned', 'overtime_hours', 'overtime_wages',
                   'gross_salary', 'pf_employee', 'esi_employee', 'advances', 'fines',
                   'total_deductions', 'net_salary', 'total_days_worked'];
        
        $totals = $this->calculateTotals($rows, $fields);

        if ($this->formCode === 'FORM_10') {
            $totals['normal_rate'] = array_sum(array_column($rows, 'normal_rate'));
            $totals['overtime_rate'] = array_sum(array_column($rows, 'overtime_rate'));
            $totals['normal_earnings'] = array_sum(array_column($rows, 'normal_earnings'));
            $totals['food_grain_benefit'] = array_sum(array_column($rows, 'food_grain_benefit'));
            $totals['piece_worker_overtime'] = array_sum(array_column($rows, 'piece_worker_overtime'));
        }

        return $totals;
    }

    private function enrichForm10Data(array $row, $record, array $rawData): array
    {
        return array_merge($row, [
            'employee_name' => $record->employee_name ?? $row['employee_name'],
            'designation' => $record->designation ?? $row['designation'],
            'normal_rate' => round($record->normal_rate ?? 0, 2),
            'overtime_rate' => round($record->overtime_rate ?? 0, 2),
            'normal_earnings' => round($record->normal_earnings ?? 0, 2),
            'overtime_wages' => round($record->overtime_wages ?? 0, 2),
            'food_grain_benefit' => round($record->food_grain_benefit ?? 0, 2),
            'is_piece_worker' => $record->is_piece_worker ?? false,
            'piece_worker_overtime' => $record->piece_worker_overtime ?? 0,
        ]);
    }

    private function enrichFormBData(array $row, $record, array $rawData): array
    {
        return [
            'employee_code' => $record->employee_code ?? 'N/A',
            'employee_name' => $record->employee_name ?? 'N/A',
            'designation' => $record->designation ?? 'N/A',
            'total_days_worked' => $record->total_days_worked ?? 0,
            'daily_rate' => round($record->daily_rate ?? 0, 2),
            'basic_earned' => round($record->basic_earned ?? 0, 2),
            'da_earned' => round($record->da_earned ?? 0, 2),
            'hra_earned' => round($record->hra_earned ?? 0, 2),
            'overtime_hours' => $record->overtime_hours ?? 0,
            'overtime_wages' => round($record->overtime_wages ?? 0, 2),
            'gross_salary' => round($record->gross_salary ?? 0, 2),
            'pf_employee' => round($record->pf_employee ?? 0, 2),
            'esi_employee' => round($record->esi_employee ?? 0, 2),
            'advances' => round($record->advances ?? 0, 2),
            'fines' => round($record->fines ?? 0, 2),
            'total_deductions' => round($record->total_deductions ?? 0, 2),
            'net_salary' => round($record->net_salary ?? 0, 2),
        ];
    }
}
