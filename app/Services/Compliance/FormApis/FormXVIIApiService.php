<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXVIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $periodStart = $this->periodStart;
        $periodEnd   = $this->periodEnd;

        $rawRows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->selectRaw("
                e.id                                    AS employee_id,
                e.employee_code,
                e.name,
                e.father_name,
                e.designation,
                COALESCE(e.uan_number, e.pf_number)    AS uan,
                e.pf_number,
                e.esi_number,
                COALESCE(NULLIF(pe.basic_earned,0), e.basic_salary, 0) AS basic_earned,
                COALESCE(pe.da_earned, 0)               AS da_earned,
                COALESCE(pe.hra_earned, 0)              AS hra_earned,
                COALESCE(pe.other_allowances, 0)        AS other_allowances,
                COALESCE(pe.overtime_wages, 0)          AS overtime_wages,
                pe.gross_salary                         AS gross_salary,
                COALESCE(pe.pf_employee, 0)             AS pf_employee,
                COALESCE(pe.esi_employee, 0)            AS esi_employee,
                COALESCE(pe.professional_tax, 0)        AS pt_deduction,
                COALESCE(pe.other_deductions, 0)        AS other_deductions,
                pe.total_deductions                     AS total_deductions,
                pe.net_salary                           AS net_salary,
                pe.total_days_worked                    AS total_days_worked,
                pe.payment_date                         AS payment_date
            ")
            ->orderBy('e.employee_code')
            ->get();

        // Pre-load attendance counts in a single query to avoid N+1
        $employeeIds     = $rawRows->pluck('employee_id')->unique()->values();
        $attendanceCounts = DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->whereIn('status', ['present', 'leave'])
            ->whereIn('employee_id', $employeeIds)
            ->selectRaw('employee_id, COUNT(*) as days_count')
            ->groupBy('employee_id')
            ->pluck('days_count', 'employee_id');

        $rows = $rawRows->map(function ($row) use ($attendanceCounts) {
                $row             = (array) $row;
                $daysWorked      = (int) ($attendanceCounts[$row['employee_id']] ?? $row['total_days_worked'] ?? 0);
                $row['days_worked'] = $daysWorked;
                $row['daily_rate']  = $daysWorked > 0
                    ? round($row['gross_salary'] / $daysWorked, 2)
                    : 0;
                return $row;
            })
            ->toArray();

        return [
            'records' => $rows,
            'meta'    => [
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
