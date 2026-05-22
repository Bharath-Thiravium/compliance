<?php

namespace App\Services\Compliance\FormGenerator;

class FormBGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_B';
    protected string $view = 'compliance.forms.form_b';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $rows = [];

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);
            $grossSalary   = (float)($record['gross_salary'] ?? 0);
            $totalDeduct   = (float)($record['total_deductions'] ?? 0);
            $netSalary     = (float)($record['net_salary'] ?? ($grossSalary - $totalDeduct));

            $rows[] = [
                'employee_code'    => $record['employee_code'] ?? 'N/A',
                'employee_name'    => $record['employee_name'] ?? 'N/A',
                'uan'              => $record['uan'] ?? '',
                'rate_of_wage'     => round((float)($record['rate_of_wage'] ?? $record['basic_earned'] ?? 0), 2),
                'total_days_worked'=> $record['total_days_worked'] ?? 0,
                'overtime_hours'   => round((float)($record['overtime_hours'] ?? 0), 2),
                'basic_earned'     => round((float)($record['basic_earned'] ?? 0), 2),
                'special_allowance'=> round((float)($record['special_allowance'] ?? 0), 2),
                'da_earned'        => round((float)($record['da_earned'] ?? 0), 2),
                'overtime_wages'   => round((float)($record['overtime_wages'] ?? 0), 2),
                'hra_earned'       => round((float)($record['hra_earned'] ?? 0), 2),
                'other_earnings'   => round((float)($record['other_earnings'] ?? 0), 2),
                'gross_salary'     => round($grossSalary, 2),
                'pf_employee'      => round((float)($record['pf_employee'] ?? 0), 2),
                'pf_employer'      => round((float)($record['pf_employer'] ?? 0), 2),
                'esi_employee'     => round((float)($record['esi_employee'] ?? 0), 2),
                'other_deductions' => round((float)($record['other_deductions'] ?? 0), 2),
                'pt_deduction'     => round((float)($record['pt_deduction'] ?? 0), 2),
                'recovery'         => round((float)($record['recovery'] ?? 0), 2),
                'total_deductions' => round($totalDeduct, 2),
                'net_salary'       => round($netSalary, 2),
                'payment_date'     => $record['payment_date'] ?? '',
                'bank_transaction_id' => $record['bank_transaction_id'] ?? '',
                'remarks'          => $record['remarks'] ?? '',
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'basic_earned', 'special_allowance', 'da_earned', 'overtime_wages',
            'hra_earned', 'other_earnings', 'gross_salary', 'pf_employee',
            'esi_employee', 'other_deductions', 'pt_deduction', 'recovery',
            'total_deductions', 'net_salary',
        ]);

        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title'         => 'FORM B - Register of Wages',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? 'N/A',
                'owner_name'         => $tenant['owner_name'] ?? $tenant['name'] ?? 'N/A',
                'branch_name'        => $branch['name'] ?? 'N/A',
                'wage_period'        => 'Monthly',
                'period'             => $this->formatPeriod($month, $year),
            ],
            'minWages' => [
                'basic'    => ['skilled' => '', 'semi_skilled' => '', 'unskilled' => ''],
                'da'       => ['skilled' => '', 'semi_skilled' => '', 'unskilled' => ''],
                'overtime' => ['skilled' => '', 'semi_skilled' => '', 'unskilled' => ''],
            ],
            'rows'    => $rows,
            'totals'  => $totals,
            'is_nil'  => count($rows) === 0,
            'entries' => $rows,
        ];
    }
}
