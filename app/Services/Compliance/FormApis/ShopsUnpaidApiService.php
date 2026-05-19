<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsUnpaidApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $data = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->select([
                DB::raw('QUARTER(pc.period_from) as quarter'),
                DB::raw('SUM(COALESCE(pe.fines, 0)) as fines_realisation'),
                DB::raw('SUM(COALESCE(pe.advances, 0)) as unpaid_basic'),
                DB::raw('0 as unpaid_overtime'),
                DB::raw('0 as unpaid_allowance'),
                DB::raw('0 as unpaid_bonus'),
                DB::raw('0 as unpaid_gratuity'),
                DB::raw('0 as unpaid_other'),
                DB::raw('0 as standing_order_deduction'),
                DB::raw('0 as pwa_deduction'),
            ])
            ->groupBy(DB::raw('QUARTER(pc.period_from)'))
            ->get()
            ->keyBy('quarter')
            ->map(fn($row) => (array)$row)
            ->toArray();

        $quarters = [
            1 => 'quarter_march',
            2 => 'quarter_june',
            3 => 'quarter_september',
            4 => 'quarter_december',
        ];

        $result = [];
        foreach ($quarters as $q => $key) {
            $result[$key] = $data[$q] ?? [
                'fines_realisation' => 0,
                'unpaid_basic' => 0,
                'unpaid_overtime' => 0,
                'unpaid_allowance' => 0,
                'unpaid_bonus' => 0,
                'unpaid_gratuity' => 0,
                'unpaid_other' => 0,
                'standing_order_deduction' => 0,
                'pwa_deduction' => 0,
            ];
        }

        return [
            'records' => $result,
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
