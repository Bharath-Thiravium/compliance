<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form10ApiService extends BaseFormApiService
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
                e.designation,
                e.department,
                pe.overtime_hours               AS overtime_hours,
                pe.overtime_wages               AS overtime_wages,
                COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) AS basic_earned,
                COALESCE(pe.da_earned, 0)       AS da_earned,
                COALESCE(pe.hra_earned, 0)      AS hra_earned,
                COALESCE(pe.other_allowances,0) AS other_allowances,
                pe.total_days_worked            AS total_days_worked
            ")
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        $totalWorkers = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->count();

        $contractor = DB::table('contractors')
            ->where('tenant_id', $tenantId)
            ->value('contractor_name');

        return [
            'records'         => $rows,
            'meta'            => [
                'tenant_id'     => $tenantId,
                'branch_id'     => $branchId,
                'month'         => $month,
                'year'          => $year,
                'total_workers' => $totalWorkers,
            ],
            'tenant'          => $this->getTenantDetails($tenantId),
            'branch'          => $this->getBranchDetails($branchId, $tenantId),
            'period'          => $this->formatPeriod(),
            'contractor_name' => $contractor ?? '',
        ];
    }
}
