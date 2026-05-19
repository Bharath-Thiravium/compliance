<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Include employees with overtime_hours OR overtime_wages > 0
        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->where(function ($q) {
                $q->where('pe.overtime_wages', '>', 0)
                  ->orWhere('pe.overtime_hours', '>', 0);
            })
            ->selectRaw("
                e.employee_code,
                e.name                                                   AS employee_name,
                e.father_name,
                e.gender                                                 AS sex,
                e.designation,
                pc.period_from                                           AS overtime_dates,
                COALESCE(pe.overtime_hours, 0)                          AS total_overtime,
                COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0)  AS normal_rate,
                COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) * 2 AS overtime_rate,
                CASE
                    WHEN pe.overtime_wages > 0 THEN pe.overtime_wages
                    WHEN pe.overtime_hours > 0
                     AND COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) > 0
                    THEN ROUND(
                        (COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) / 26 / 8)
                        * 2 * pe.overtime_hours, 2)
                    ELSE 0
                END                                                      AS overtime_earnings,
                pc.period_to                                             AS payment_date,
                ''                                                       AS remarks
            ")
            ->orderBy('e.name')
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
