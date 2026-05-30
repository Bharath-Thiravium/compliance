<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormDERApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('pe.tenant_id', $tenantId)
            ->where('pe.branch_id', $branchId)
            ->whereNull('pe.deleted_at')
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.employee_code',
                'e.name',
                'e.gender',
                'e.designation',
                DB::raw('SUM(pe.basic_earned) as basic_earned'),
                DB::raw('SUM(COALESCE(pe.da_earned, 0)) as da_earned'),
                DB::raw('SUM(COALESCE(pe.hra_earned, 0)) as hra_earned'),
                DB::raw('SUM(COALESCE(pe.other_allowances, 0)) as other_allowances'),
                DB::raw('SUM(pe.gross_salary) as gross_salary'),
            ])
            ->groupBy('e.employee_code', 'e.name', 'e.gender', 'e.designation')
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records'      => $rows,
            'record_count' => count($rows),
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
