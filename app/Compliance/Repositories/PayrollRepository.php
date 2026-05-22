<?php

namespace App\Compliance\Repositories;

use App\Models\WorkforcePayrollEntry;
use Illuminate\Support\Collection;

class PayrollRepository
{
    public function getByPeriod(int $tenantId, int $month, int $year): Collection
    {
        return WorkforcePayrollEntry::with(['employee', 'payrollCycle'])
            ->where('tenant_id', $tenantId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                    ->whereYear('period_from', $year);
            })
            ->get();
    }

    public function getByBranchAndPeriod(int $tenantId, int $branchId, int $month, int $year): Collection
    {
        return WorkforcePayrollEntry::with(['employee', 'payrollCycle'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                    ->whereYear('period_from', $year);
            })
            ->get();
    }

    public function getByEmployee(int $employeeId, int $month, int $year): ?WorkforcePayrollEntry
    {
        return WorkforcePayrollEntry::with(['payrollCycle'])
            ->where('employee_id', $employeeId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                    ->whereYear('period_from', $year);
            })
            ->first();
    }

    public function getTotalDeductions(int $tenantId, int $month, int $year): float
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                    ->whereYear('period_from', $year);
            })
            ->sum('total_deductions') ?? 0;
    }

    public function getTotalAdvances(int $tenantId, int $month, int $year): float
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                    ->whereYear('period_from', $year);
            })
            ->sum('advances') ?? 0;
    }

    public function getTotalFines(int $tenantId, int $month, int $year): float
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                    ->whereYear('period_from', $year);
            })
            ->sum('fines') ?? 0;
    }
}
