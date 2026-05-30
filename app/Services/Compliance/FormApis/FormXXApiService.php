<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $employeeIds = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($employeeIds->isEmpty()) {
            return $this->emptyResponse($tenantId, $branchId, $month, $year);
        }

        // Source 1: dedicated workforce_deductions table (one row per deduction event)
        if (Schema::hasTable('workforce_deductions')) {
            $deductions = DB::table('workforce_deductions as d')
                ->join('workforce_employee as e', 'e.id', '=', 'd.employee_id')
                ->where('d.tenant_id', $tenantId)
                ->where('d.branch_id', $branchId)
                ->whereBetween('d.deduction_date', [$this->periodStart, $this->periodEnd])
                ->whereNull('d.deleted_at')
                ->whereIn('d.employee_id', $employeeIds)
                ->where('d.amount', '>', 0)
                ->select([
                    'e.employee_code',
                    'e.name as employee_name',
                    'e.father_name',
                    'e.designation',
                    DB::raw("COALESCE(d.particulars, d.deduction_type, '') as damage_particulars"),
                    'd.deduction_date as damage_date',
                    'd.showed_cause',
                    'd.witness_name',
                    'd.amount as deduction_amount',
                    'd.num_instalments as instalments',
                    'd.first_month',
                    'd.last_month',
                    'd.remarks',
                ])
                ->orderBy('e.employee_code')
                ->orderBy('d.deduction_date')
                ->get()
                // Composite dedup: same employee + same date + same amount = duplicate DB row
                ->unique(fn($r) => implode('|', [
                    $r->employee_code   ?? '',
                    $r->damage_date     ?? '',
                    (string)($r->deduction_amount ?? ''),
                ]))
                ->values()
                ->map(function ($r) {
                    $r = (array) $r;
                    $r['damage_date']  = $r['damage_date']  ? date('d/m/Y', strtotime($r['damage_date']))  : '';
                    $r['first_month']  = $r['first_month']  ? date('m/Y',   strtotime($r['first_month']))  : '';
                    $r['last_month']   = $r['last_month']   ? date('m/Y',   strtotime($r['last_month']))   : '';
                    $r['showed_cause'] = $r['showed_cause'] ? 'Yes' : 'No';
                    return $r;
                })
                ->toArray();

            if (!empty($deductions)) {
                return $this->buildResponse($deductions, $tenantId, $branchId, $month, $year);
            }
        }

        // Source 2: payroll other_deductions fallback — use only the latest cycle to prevent multi-cycle duplication
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->orderByDesc('id')
            ->value('id');

        if (!$cycleId) {
            return $this->emptyResponse($tenantId, $branchId, $month, $year);
        }

        $records = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('pe.payroll_cycle_id', $cycleId)
            ->whereIn('pe.employee_id', $employeeIds)
            ->where('pe.other_deductions', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                DB::raw("COALESCE(pe.damage_particulars, '') as damage_particulars"),
                'pc.period_from as damage_date',
                DB::raw("'' as showed_cause"),
                DB::raw("'' as witness_name"),
                'pe.other_deductions as deduction_amount',
                DB::raw("'' as instalments"),
                DB::raw("'' as first_month"),
                DB::raw("'' as last_month"),
                DB::raw("'' as remarks"),
            ])
            ->orderBy('e.employee_code')
            ->get()
            // Composite dedup: same employee + same date + same amount
            ->unique(fn($r) => implode('|', [
                $r->employee_code      ?? '',
                $r->damage_date        ?? '',
                (string)($r->deduction_amount ?? ''),
            ]))
            ->values()
            ->map(function ($r) {
                $r = (array) $r;
                $r['damage_date']  = $r['damage_date'] ? date('d/m/Y', strtotime($r['damage_date'])) : '';
                $r['showed_cause'] = $r['showed_cause'] ? 'Yes' : 'No';
                return $r;
            })
            ->toArray();

        return $this->buildResponse($records, $tenantId, $branchId, $month, $year);
    }

    private function buildResponse(array $records, int $tenantId, int $branchId, int $month, int $year): array
    {
        return [
            'records'      => $records,
            'record_count' => count($records),
            'meta'         => ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
            'tenant'  => $this->getTenantDetails($tenantId),
            'branch'  => $this->getBranchDetails($branchId, $tenantId),
            'period'  => $this->formatPeriod(),
        ];
    }

    private function emptyResponse(int $tenantId, int $branchId, int $month, int $year): array
    {
        return $this->buildResponse([], $tenantId, $branchId, $month, $year);
    }
}
