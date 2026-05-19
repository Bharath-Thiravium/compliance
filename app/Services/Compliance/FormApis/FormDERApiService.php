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
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.employee_code',
                'e.name',
                'e.gender',
                'e.designation',
                'pe.basic_earned',
                DB::raw('COALESCE(pe.da_earned, 0) as da_earned'),
                DB::raw('COALESCE(pe.hra_earned, 0) as hra_earned'),
                DB::raw('COALESCE(pe.other_allowances, 0) as other_allowances'),
                'pe.gross_salary',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
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
