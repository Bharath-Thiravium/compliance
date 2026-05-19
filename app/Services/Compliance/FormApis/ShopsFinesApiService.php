<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsFinesApiService extends BaseFormApiService
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
            ->where('pe.fines', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'pe.fines as fine_amount',
                DB::raw('"" as reason'),
                DB::raw('NULL as fine_date'),
                'pe.gross_salary as wages',
                'pc.period_to as realized_date',
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
