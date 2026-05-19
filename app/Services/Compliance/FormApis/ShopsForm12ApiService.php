<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsForm12ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_advances as wa')
            ->join('workforce_employee as e', 'e.id', '=', 'wa.employee_id')
            ->where('wa.tenant_id', $tenantId)
            ->where('wa.branch_id', $branchId)
            ->whereYear('wa.advance_date', $year)
            ->whereMonth('wa.advance_date', $month)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'wa.amount as advance_amount',
                'wa.advance_date',
                DB::raw("'Advance' as advance_purpose"),
                'wa.num_instalments as advance_installments',
                DB::raw("NULL as advance_postponements"),
                DB::raw("NULL as advance_repaid_date"),
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
