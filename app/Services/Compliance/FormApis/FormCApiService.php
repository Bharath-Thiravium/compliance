<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormCApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // --- All employees (base list) ---
        $employees = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->orderBy('e.employee_code')
            ->pluck('e.name', 'e.id')
            ->toArray();

        // --- Advances ---
        $advances = DB::table('workforce_advances as a')
            ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.branch_id', $branchId)
            ->whereYear('a.advance_date', $year)
            ->whereMonth('a.advance_date', $month)
            ->select([
                'e.name as employee_name',
                DB::raw("'Advance' as recovery_type"),
                DB::raw("'' as particulars"),
                DB::raw("'' as damage_date"),
                'a.amount',
                DB::raw("'' as show_cause"),
                DB::raw("'' as explanation"),
                'a.num_instalments as installments',
                'a.first_month',
                'a.last_month',
                DB::raw("'' as recovery_date"),
                'a.remarks',
            ])
            ->get()
            ->map(fn($r) => (array)$r)
            ->toArray();

        // --- Fines ---
        $fines = DB::table('workforce_fines as f')
            ->join('workforce_employee as e', 'e.id', '=', 'f.employee_id')
            ->where('f.tenant_id', $tenantId)
            ->where('f.branch_id', $branchId)
            ->whereYear('f.fine_date', $year)
            ->whereMonth('f.fine_date', $month)
            ->select([
                'e.name as employee_name',
                DB::raw("'Fine' as recovery_type"),
                'f.reason as particulars',
                'f.fine_date as damage_date',
                'f.amount',
                DB::raw("'' as show_cause"),
                DB::raw("'' as explanation"),
                DB::raw("1 as installments"),
                DB::raw("'' as first_month"),
                DB::raw("'' as last_month"),
                DB::raw("'' as recovery_date"),
                'f.remarks',
            ])
            ->get()
            ->map(fn($r) => (array)$r)
            ->toArray();

        // --- Payroll-level deductions (other_deductions from payroll entry) ---
        $payrollDeductions = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->where('pe.other_deductions', '>', 0)
            ->select([
                'e.name as employee_name',
                DB::raw("'Recovery' as recovery_type"),
                DB::raw("'Other Deduction' as particulars"),
                DB::raw("'' as damage_date"),
                'pe.other_deductions as amount',
                DB::raw("'' as show_cause"),
                DB::raw("'' as explanation"),
                DB::raw("1 as installments"),
                DB::raw("'' as first_month"),
                DB::raw("'' as last_month"),
                DB::raw("'' as recovery_date"),
                DB::raw("'' as remarks"),
            ])
            ->get()
            ->map(fn($r) => (array)$r)
            ->toArray();

        // Build keyed lookup: employee_name => [rows]
        $dataByEmployee = [];
        foreach (array_merge($advances, $fines, $payrollDeductions) as $r) {
            $dataByEmployee[$r['employee_name']][] = $r;
        }

        // One row per employee — use actual data if exists, else all-empty defaults
        $records = [];
        foreach ($employees as $empId => $empName) {
            if (!empty($dataByEmployee[$empName])) {
                foreach ($dataByEmployee[$empName] as $r) {
                    $records[] = $r;
                }
            } else {
                $records[] = [
                    'employee_name' => $empName,
                    'recovery_type' => '',
                    'particulars'   => '',
                    'damage_date'   => '',
                    'amount'        => '',
                    'show_cause'    => '',
                    'explanation'   => '',
                    'installments'  => '',
                    'first_month'   => '',
                    'last_month'    => '',
                    'recovery_date' => '',
                    'remarks'       => '',
                ];
            }
        }

        return [
            'records' => $records,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
