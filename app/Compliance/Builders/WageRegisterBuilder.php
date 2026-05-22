<?php

namespace App\Compliance\Builders;

class WageRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $entries = $this->payrollRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);

        if ($entries->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $entries->map(fn($entry) => [
            'employee_code' => $entry->employee->employee_code ?? 'N/A',
            'employee_name' => $entry->employee->name ?? 'N/A',
            'designation' => $entry->employee->designation ?? 'N/A',
            'basic_earned' => $entry->basic_earned ?? 0,
            'special_allowance' => $entry->other_allowances ?? 0,
            'da_earned' => $entry->da_earned ?? 0,
            'overtime_wages' => $entry->overtime_wages ?? 0,
            'hra_earned' => $entry->hra_earned ?? 0,
            'other_earnings' => 0,
            'gross_salary' => $entry->gross_salary ?? 0,
            'pf_employee' => $entry->pf_employee ?? 0,
            'esi_employee' => $entry->esi_employee ?? 0,
            'other_deductions' => $entry->other_deductions ?? 0,
            'pt_deduction' => $entry->professional_tax ?? 0,
            'recovery' => 0,
            'total_deductions' => $entry->total_deductions ?? 0,
            'net_salary' => $entry->net_salary ?? 0,
            'total_days_worked' => $entry->total_days_worked ?? 0,
            'overtime_hours' => $entry->overtime_hours ?? 0,
            'payment_date' => $entry->payment_date ?? '',
            'remarks' => '',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'totals' => [
                'basic_earned' => $entries->sum('basic_earned'),
                'special_allowance' => $entries->sum('other_allowances'),
                'da_earned' => $entries->sum('da_earned'),
                'overtime_wages' => $entries->sum('overtime_wages'),
                'hra_earned' => $entries->sum('hra_earned'),
                'other_earnings' => 0,
                'gross_salary' => $entries->sum('gross_salary'),
                'pf_employee' => $entries->sum('pf_employee'),
                'esi_employee' => $entries->sum('esi_employee'),
                'other_deductions' => $entries->sum('other_deductions'),
                'pt_deduction' => $entries->sum('professional_tax'),
                'recovery' => 0,
                'total_deductions' => $entries->sum('total_deductions'),
                'net_salary' => $entries->sum('net_salary'),
            ],
        ];
    }
}
