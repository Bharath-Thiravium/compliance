<?php

namespace App\Compliance\Repositories;

use App\Models\WorkforcePayrollEntry;
use Illuminate\Support\Collection;

class DeductionRepository
{
    public function getByPeriod(int $tenantId, int $month, int $year): Collection
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                  ->whereYear('period_from', $year);
            })
            ->with('employee')
            ->get();
    }

    public function getByBranchAndPeriod(int $tenantId, int $branchId, int $month, int $year): Collection
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                  ->whereYear('period_from', $year);
            })
            ->with('employee')
            ->get();
    }

    public function getAdvances(int $tenantId, int $month, int $year): Collection
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->where('advances', '>', 0)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                  ->whereYear('period_from', $year);
            })
            ->with('employee')
            ->get();
    }

    public function getFines(int $tenantId, int $month, int $year): Collection
    {
        return WorkforcePayrollEntry::where('tenant_id', $tenantId)
            ->where('fines', '>', 0)
            ->whereHas('payrollCycle', function ($q) use ($month, $year) {
                $q->whereMonth('period_from', $month)
                  ->whereYear('period_from', $year);
            })
            ->with('employee')
            ->get();
    }
}
