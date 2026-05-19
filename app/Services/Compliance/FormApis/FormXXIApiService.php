<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormXXIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $periodStart = $this->periodStart;
        $periodEnd   = $this->periodEnd;

        // ── All active employees ──────────────────────────────────────────────
        $employees = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->whereNull('e.deleted_at')
            ->select(['e.id', 'e.employee_code', 'e.name as employee_name', 'e.father_name', 'e.designation'])
            ->orderBy('e.employee_code')
            ->get();

        $employeeIds = $employees->pluck('id');

        // ── Source 1: fines recorded in payroll_entry for this period ──────────
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->value('id');

        $payrollFines = collect();
        if ($cycleId) {
            $payrollFines = DB::table('workforce_payroll_entry as pe')
                ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
                ->where('pe.payroll_cycle_id', $cycleId)
                ->where('pe.fines', '>', 0)
                ->whereIn('pe.employee_id', $employeeIds)
                ->select([
                    'pe.employee_id',
                    DB::raw('pe.fines as fine_amount'),
                    DB::raw('pc.period_from as fine_date'),
                    DB::raw('"Misconduct" as reason'),
                ])
                ->get()
                ->keyBy('employee_id');
        }

        // ── Source 2: dedicated workforce_fines table ──────────────────────────
        $tableFines = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('workforce_fines')) {
            $tableFines = DB::table('workforce_fines')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->whereBetween('fine_date', [$periodStart, $periodEnd])
                ->whereNull('deleted_at')
                ->whereIn('employee_id', $employeeIds)
                ->select(['employee_id', 'amount as fine_amount', 'fine_date', 'reason', 'remarks'])
                ->get()
                ->groupBy('employee_id');
        }

        // ── Merge: prefer workforce_fines (granular) over payroll_entry lump ───
        $rows = [];
        foreach ($employees as $emp) {
            $emp = (array) $emp;
            $empId = $emp['id'];

            if ($tableFines->has($empId)) {
                // One row per recorded fine
                foreach ($tableFines->get($empId) as $fine) {
                    $fine = (array) $fine;
                    $fineDate = Carbon::parse($fine['fine_date']);
                    $rows[] = [
                        'employee_code'   => $emp['employee_code'],
                        'employee_name'   => $emp['employee_name'],
                        'father_name'     => $emp['father_name'],
                        'designation'     => $emp['designation'],
                        'act_or_omission' => $fine['reason'] ?? 'Misconduct',
                        'date_of_offence' => $fineDate->format('d/m/Y'),
                        'showed_cause'    => 'Yes',
                        'heard_by'        => 'Manager',
                        'fine_amount'     => $fine['fine_amount'],
                        'fine_realised'   => $fineDate->format('d/m/Y'),
                        'wage_period'     => $fineDate->format('F Y'),
                        'remarks'         => $fine['remarks'] ?? null,
                    ];
                }
            } elseif ($payrollFines->has($empId)) {
                $pf       = (array) $payrollFines->get($empId);
                $fineDate = Carbon::parse($pf['fine_date']);
                $rows[] = [
                    'employee_code'   => $emp['employee_code'],
                    'employee_name'   => $emp['employee_name'],
                    'father_name'     => $emp['father_name'],
                    'designation'     => $emp['designation'],
                    'act_or_omission' => $pf['reason'] ?? 'Misconduct',
                    'date_of_offence' => $fineDate->format('d/m/Y'),
                    'showed_cause'    => 'Yes',
                    'heard_by'        => 'Manager',
                    'fine_amount'     => $pf['fine_amount'],
                    'fine_realised'   => $fineDate->format('d/m/Y'),
                    'wage_period'     => $fineDate->format('F Y'),
                    'remarks'         => null,
                ];
            } else {
                // No fine — included with nulls so generator can render NIL row
                $rows[] = [
                    'employee_code'   => $emp['employee_code'],
                    'employee_name'   => $emp['employee_name'],
                    'father_name'     => $emp['father_name'],
                    'designation'     => $emp['designation'],
                    'act_or_omission' => null,
                    'date_of_offence' => null,
                    'showed_cause'    => null,
                    'heard_by'        => null,
                    'fine_amount'     => null,
                    'fine_realised'   => null,
                    'wage_period'     => null,
                    'remarks'         => null,
                ];
            }
        }

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant'  => $this->getTenantDetails($tenantId),
            'branch'  => $this->getBranchDetails($branchId, $tenantId),
            'period'  => $this->formatPeriod(),
            'is_nil'  => count($rows) === 0,
        ];
    }
}
