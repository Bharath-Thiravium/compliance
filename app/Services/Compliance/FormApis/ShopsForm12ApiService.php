<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsForm12ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Source 1: dedicated workforce_advances table (one row per advance — legitimate multiples)
        $rows = DB::table('workforce_advances as wa')
            ->join('workforce_employee as e', 'e.id', '=', 'wa.employee_id')
            ->where('wa.tenant_id', $tenantId)
            ->where('wa.branch_id', $branchId)
            ->whereYear('wa.advance_date', $year)
            ->whereMonth('wa.advance_date', $month)
            ->whereNull('wa.deleted_at')
            ->whereNull('e.deleted_at')
            ->where('wa.amount', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'wa.amount as advance_amount',
                'wa.advance_date',
                'wa.purpose as advance_purpose',
                'wa.num_instalments as advance_installments',
                DB::raw('NULL as advance_postponements'),
                DB::raw('NULL as advance_repaid_date'),
            ])
            ->orderBy('e.employee_code')
            ->orderBy('wa.advance_date')
            ->get()
            // Composite dedup: same employee + same date + same amount = duplicate DB row
            ->unique(fn($r) => implode('|', [
                $r->employee_code  ?? '',
                $r->advance_date   ?? '',
                (string)($r->advance_amount ?? ''),
            ]))
            ->values()
            ->map(fn($r) => (array) $r)
            ->toArray();

        if (!empty($rows)) {
            return $this->buildResponse($rows, $tenantId, $branchId, $month, $year);
        }

        // Source 2: payroll_entry.advances fallback — use only the latest cycle
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->orderByDesc('id')
            ->value('id');

        if (!$cycleId) {
            return $this->buildResponse([], $tenantId, $branchId, $month, $year);
        }

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereNull('e.deleted_at')
            ->where('pe.payroll_cycle_id', $cycleId)
            ->where('pe.advances', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'pe.advances as advance_amount',
                'pc.period_from as advance_date',
                DB::raw('"Salary Advance" as advance_purpose'),
                DB::raw('1 as advance_installments'),
                DB::raw('NULL as advance_postponements'),
                DB::raw('NULL as advance_repaid_date'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            // Composite dedup: same employee + same date + same amount
            ->unique(fn($r) => implode('|', [
                $r->employee_code  ?? '',
                $r->advance_date   ?? '',
                (string)($r->advance_amount ?? ''),
            ]))
            ->values()
            ->map(fn($r) => (array) $r)
            ->toArray();

        return $this->buildResponse($rows, $tenantId, $branchId, $month, $year);
    }

    private function buildResponse(array $rows, int $tenantId, int $branchId, int $month, int $year): array
    {
        return [
            'records' => $rows,
            'meta'    => ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
            'tenant'  => $this->getTenantDetails($tenantId),
            'branch'  => $this->getBranchDetails($branchId, $tenantId),
            'period'  => $this->formatPeriod(),
        ];
    }
}
