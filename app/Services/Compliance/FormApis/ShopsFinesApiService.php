<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsFinesApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Source 1: dedicated workforce_fines table (one row per fine event — legitimate multiples)
        $tableFines = DB::table('workforce_fines as wf')
            ->join('workforce_employee as e', 'e.id', '=', 'wf.employee_id')
            ->where('wf.tenant_id', $tenantId)
            ->where('wf.branch_id', $branchId)
            ->whereYear('wf.fine_date', $year)
            ->whereMonth('wf.fine_date', $month)
            ->whereNull('wf.deleted_at')
            ->whereNull('e.deleted_at')
            ->where('wf.amount', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'wf.amount as fine_amount',
                'wf.reason',
                'wf.fine_date',
                'e.basic_salary as wages',
                'wf.fine_date as realized_date',
                'wf.remarks',
            ])
            ->orderBy('e.employee_code')
            ->orderBy('wf.fine_date')
            ->get()
            // Composite dedup: same employee + same date + same amount = duplicate DB row
            ->unique(fn($r) => implode('|', [
                $r->employee_code ?? '',
                $r->fine_date     ?? '',
                (string)($r->fine_amount ?? ''),
            ]))
            ->values()
            ->map(fn($r) => (array) $r)
            ->toArray();

        if (!empty($tableFines)) {
            return $this->buildResponse($tableFines, $tenantId, $branchId, $month, $year);
        }

        // Source 2: payroll_entry.fines fallback — use only the latest cycle
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->orderByDesc('id')
            ->value('id');

        if (!$cycleId) {
            return $this->buildResponse([], $tenantId, $branchId, $month, $year);
        }

        $payrollFines = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereNull('e.deleted_at')
            ->where('pe.payroll_cycle_id', $cycleId)
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
                DB::raw('"" as remarks'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            // Composite dedup: same employee + same amount (fine_date is NULL from payroll)
            ->unique(fn($r) => implode('|', [
                $r->employee_code ?? '',
                (string)($r->fine_amount ?? ''),
            ]))
            ->values()
            ->map(fn($r) => (array) $r)
            ->toArray();

        return $this->buildResponse($payrollFines, $tenantId, $branchId, $month, $year);
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
