<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormCApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Advances — one row per advance record (legitimate multiple per employee)
        $advances = DB::table('workforce_advances as a')
            ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.branch_id', $branchId)
            ->whereYear('a.advance_date', $year)
            ->whereMonth('a.advance_date', $month)
            ->whereNull('a.deleted_at')
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
            ->orderBy('e.name')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        // Fines — one row per fine record (legitimate multiple per employee)
        $fines = DB::table('workforce_fines as f')
            ->join('workforce_employee as e', 'e.id', '=', 'f.employee_id')
            ->where('f.tenant_id', $tenantId)
            ->where('f.branch_id', $branchId)
            ->whereYear('f.fine_date', $year)
            ->whereMonth('f.fine_date', $month)
            ->whereNull('f.deleted_at')
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
            ->orderBy('e.name')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        // Payroll deductions — one row per employee (deduplicated by employee_id)
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->value('id');

        $payrollDeductions = [];
        if ($cycleId) {
            $payrollDeductions = DB::table('workforce_payroll_entry as pe')
                ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
                ->where('pe.payroll_cycle_id', $cycleId)
                ->where('e.tenant_id', $tenantId)
                ->where('e.branch_id', $branchId)
                ->where('pe.other_deductions', '>', 0)
                ->select([
                    'e.id as employee_id',
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
                ->orderBy('e.name')
                ->get()
                // Deduplicate: one payroll deduction row per employee per cycle
                ->unique('employee_id')
                ->values()
                ->map(fn($r) => collect((array) $r)->except('employee_id')->toArray())
                ->toArray();
        }

        // Merge all sources — only employees with actual data appear
        $records = array_merge($advances, $fines, $payrollDeductions);

        return [
            'records'      => $records,
            'record_count' => count($records),
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
