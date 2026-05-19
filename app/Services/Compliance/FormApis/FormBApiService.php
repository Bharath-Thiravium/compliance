<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormBApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->selectRaw("
                e.employee_code,
                e.name                                          AS employee_name,
                COALESCE(e.uan_number, e.pf_number, '')        AS uan,
                COALESCE(e.basic_salary, pe.basic_earned, 0)   AS rate_of_wage,
                COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) AS basic_earned,
                pe.total_days_worked                            AS total_days_worked,
                COALESCE(pe.overtime_hours, 0)                 AS overtime_hours,
                COALESCE(pe.da_earned, 0)                      AS da_earned,
                COALESCE(pe.hra_earned, 0)                     AS hra_earned,
                COALESCE(pe.other_allowances, 0)               AS special_allowance,
                COALESCE(pe.overtime_wages, 0)                 AS overtime_wages,
                0                                              AS other_earnings,
                pe.gross_salary                                AS gross_salary,
                COALESCE(pe.pf_employee, 0)                    AS pf_employee,
                0                                              AS pf_employer,
                COALESCE(pe.esi_employee, 0)                   AS esi_employee,
                COALESCE(pe.other_deductions, 0)               AS other_deductions,
                COALESCE(pe.professional_tax, 0)               AS pt_deduction,
                COALESCE(pe.advances, 0)                       AS recovery,
                pe.total_deductions                            AS total_deductions,
                pe.net_salary                                  AS net_salary,
                COALESCE(pe.payment_date, '')                  AS payment_date,
                COALESCE(pe.transaction_reference, '')         AS bank_transaction_id,
                ''                                             AS remarks
            ")
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        return [
            'records' => $rows,
            'meta'    => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
