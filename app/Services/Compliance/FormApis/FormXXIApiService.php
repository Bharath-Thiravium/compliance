<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FormXXIApiService extends BaseFormApiService
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
            return $this->buildResponse([], $tenantId, $branchId, $month, $year);
        }

        // Source 1: dedicated workforce_fines table (one row per fine event — legitimate multiples)
        if (Schema::hasTable('workforce_fines')) {
            $tableFines = DB::table('workforce_fines as f')
                ->join('workforce_employee as e', 'e.id', '=', 'f.employee_id')
                ->where('f.tenant_id', $tenantId)
                ->where('f.branch_id', $branchId)
                ->whereBetween('f.fine_date', [$this->periodStart, $this->periodEnd])
                ->whereNull('f.deleted_at')
                ->whereIn('f.employee_id', $employeeIds)
                ->where('f.amount', '>', 0)
                ->select([
                    'e.employee_code',
                    'e.name as employee_name',
                    'e.father_name',
                    'e.designation',
                    'f.amount as fine_amount',
                    'f.fine_date',
                    'f.reason',
                    'f.remarks',
                ])
                ->orderBy('e.employee_code')
                ->orderBy('f.fine_date')
                ->get()
                // Composite dedup: same employee + same date + same amount = duplicate DB row
                ->unique(fn($r) => implode('|', [
                    $r->employee_code ?? '',
                    $r->fine_date     ?? '',
                    (string)($r->fine_amount ?? ''),
                ]))
                ->values()
                ->map(function ($r) {
                    $r        = (array) $r;
                    $fineDate = Carbon::parse($r['fine_date']);
                    return [
                        'employee_code'   => $r['employee_code'],
                        'employee_name'   => $r['employee_name'],
                        'father_name'     => $r['father_name']  ?? '',
                        'designation'     => $r['designation']  ?? '',
                        'act_or_omission' => $r['reason']       ?? 'Misconduct',
                        'date_of_offence' => $fineDate->format('d/m/Y'),
                        'showed_cause'    => 'Yes',
                        'heard_by'        => 'Manager',
                        'fine_amount'     => $r['fine_amount'],
                        'fine_realised'   => $fineDate->format('d/m/Y'),
                        'wage_period'     => $fineDate->format('F Y'),
                        'remarks'         => $r['remarks'] ?? '',
                    ];
                })
                ->toArray();

            if (!empty($tableFines)) {
                return $this->buildResponse($tableFines, $tenantId, $branchId, $month, $year);
            }
        }

        // Source 2: payroll fines fallback — use only the latest cycle to prevent multi-cycle duplication
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
            ->where('pe.payroll_cycle_id', $cycleId)
            ->whereIn('pe.employee_id', $employeeIds)
            ->where('pe.fines', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                'pe.fines as fine_amount',
                'pc.period_from as fine_date',
            ])
            ->orderBy('e.employee_code')
            ->get()
            // Composite dedup: same employee + same date + same amount
            ->unique(fn($r) => implode('|', [
                $r->employee_code ?? '',
                $r->fine_date     ?? '',
                (string)($r->fine_amount ?? ''),
            ]))
            ->values()
            ->map(function ($r) {
                $r        = (array) $r;
                $fineDate = Carbon::parse($r['fine_date']);
                return [
                    'employee_code'   => $r['employee_code'],
                    'employee_name'   => $r['employee_name'],
                    'father_name'     => $r['father_name']  ?? '',
                    'designation'     => $r['designation']  ?? '',
                    'act_or_omission' => 'Misconduct',
                    'date_of_offence' => $fineDate->format('d/m/Y'),
                    'showed_cause'    => 'Yes',
                    'heard_by'        => 'Manager',
                    'fine_amount'     => $r['fine_amount'],
                    'fine_realised'   => $fineDate->format('d/m/Y'),
                    'wage_period'     => $fineDate->format('F Y'),
                    'remarks'         => '',
                ];
            })
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
            'is_nil'  => empty($rows),
        ];
    }
}
