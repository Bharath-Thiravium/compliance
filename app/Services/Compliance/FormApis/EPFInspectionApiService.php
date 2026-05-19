<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class EPFInspectionApiService extends BaseFormApiService
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
                e.name,
                e.father_name,
                e.designation,
                COALESCE(e.uan_number, e.pf_number) AS uan,
                e.pf_number,
                e.esi_number,
                e.date_of_joining,
                COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) AS basic_earned,
                COALESCE(pe.da_earned, 0)           AS da_earned,
                pe.gross_salary                     AS gross_salary,
                COALESCE(pe.pf_employee, 0)         AS pf_employee,
                COALESCE(pe.pf_employee, 0)         AS pf_employer,
                COALESCE(pe.esi_employee, 0)        AS esi_employee,
                pe.total_days_worked                AS total_days_worked,
                pe.net_salary                       AS net_salary
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
