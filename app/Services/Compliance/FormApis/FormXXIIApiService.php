<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormXXIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $employeeIds = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($employeeIds->isEmpty()) {
            return $this->buildResponse([], $tenantId, $branchId, $month, $year);
        }

        // Source 1: dedicated workforce_advances table (one row per advance event)
        if (Schema::hasTable('workforce_advances')) {
            $tableAdvances = DB::table('workforce_advances as a')
                ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
                ->where('a.tenant_id', $tenantId)
                ->where('a.branch_id', $branchId)
                ->whereBetween('a.advance_date', [$this->periodStart, $this->periodEnd])
                ->whereNull('a.deleted_at')
                ->whereIn('a.employee_id', $employeeIds)
                ->where('a.amount', '>', 0)
                ->select([
                    'e.employee_code',
                    'e.name as employee_name',
                    'e.father_name',
                    'e.designation',
                    'a.amount as advance_amount',
                    'a.advance_date',
                    'a.num_instalments as installments',
                    'a.last_month',
                    'a.remarks',
                ])
                ->orderBy('e.employee_code')
                ->orderBy('a.advance_date')
                ->get()
                // Composite dedup: same employee + same date + same amount = duplicate DB row
                ->unique(fn($r) => implode('|', [
                    $r->employee_code  ?? '',
                    $r->advance_date   ?? '',
                    (string)($r->advance_amount ?? ''),
                ]))
                ->values()
                ->map(function ($r) {
                    $r = (array) $r;
                    return array_merge($r, [
                        'purpose'               => 'Salary Advance',
                        'installment_repaid'    => $r['last_month'] ?? null,
                        'last_installment_date' => $r['last_month'] ?? null,
                    ]);
                })
                ->toArray();

            if (!empty($tableAdvances)) {
                return $this->buildResponse($tableAdvances, $tenantId, $branchId, $month, $year);
            }
        }

        // Source 2: payroll advances fallback — use only the latest cycle to prevent multi-cycle duplication
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->orderByDesc('id')
            ->value('id');

        if (!$cycleId) {
            return $this->buildResponse([], $tenantId, $branchId, $month, $year);
        }

        $records = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('pe.payroll_cycle_id', $cycleId)
            ->whereIn('pe.employee_id', $employeeIds)
            ->where('pe.advances', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                'pe.advances as advance_amount',
                'pc.period_from as advance_date',
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
            ->map(fn($r) => array_merge((array) $r, [
                'purpose'               => 'Salary Advance',
                'installments'          => 1,
                'installment_repaid'    => null,
                'last_installment_date' => null,
                'remarks'               => null,
            ]))
            ->toArray();

        return $this->buildResponse($records, $tenantId, $branchId, $month, $year);
    }

    private function buildResponse(array $records, int $tenantId, int $branchId, int $month, int $year): array
    {
        return [
            'records' => $records,
            'meta'    => ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
            'tenant'  => $this->getTenantDetails($tenantId),
            'branch'  => $this->getBranchDetails($branchId, $tenantId),
            'period'  => $this->formatPeriod(),
        ];
    }
}
