<?php

namespace App\Compliance\Repositories;

use App\Models\BonusRecord;
use Illuminate\Support\Collection;

class BonusRepository
{
    public function getByPeriod(int $tenantId, int $month, int $year): Collection
    {
        return BonusRecord::where('tenant_id', $tenantId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->with('employee')
            ->get();
    }

    public function getByBranchAndPeriod(int $tenantId, int $branchId, int $month, int $year): Collection
    {
        return BonusRecord::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->with('employee')
            ->get();
    }

    public function getTotalBonus(int $tenantId, int $month, int $year): float
    {
        return BonusRecord::where('tenant_id', $tenantId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('bonus_amount') ?? 0;
    }

    public function getUnpaid(int $tenantId, int $month, int $year): Collection
    {
        return BonusRecord::where('tenant_id', $tenantId)
            ->where('status', 'unpaid')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->with('employee')
            ->get();
    }
}
