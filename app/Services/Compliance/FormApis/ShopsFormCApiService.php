<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsFormCApiService extends BaseFormApiService
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
                e.name                                  AS employee_name,
                e.father_name,
                e.date_of_birth,
                e.designation,
                pe.total_days_worked                    AS days_worked,
                pe.gross_salary                         AS total_wages,
                0                                       AS bonus_amount,
                0                                       AS puja_bonus,
                0                                       AS interim_bonus,
                COALESCE(pe.professional_tax, 0)        AS tax_deducted,
                COALESCE(pe.other_deductions, 0)        AS loss_deduction,
                0                                       AS bonus_paid,
                NULL                                    AS bonus_payment_date
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
