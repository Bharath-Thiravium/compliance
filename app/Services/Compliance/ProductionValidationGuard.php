<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;

class ProductionValidationGuard
{
    public function validateBeforeGeneration(int $tenantId, int $branchId, int $month, int $year): void
    {
        // Skip auth check in CLI context
        if (app()->runningInConsole() && !auth()->check()) {
            return;
        }

        $user = auth()->user();

        if (!$user) {
            throw new \Exception("User not authenticated");
        }

        // Bug fix 1: load subscription from DB, not from potentially stale relation
        $tenantRow = DB::table('tenants')->where('id', $tenantId)->first();
        $subscription = strtoupper($tenantRow->subscription_type ?? 'MINIMAL');
        if ($subscription !== 'FULL') {
            throw new \Exception(
                "Form generation requires FULL subscription. Current: {$subscription}"
            );
        }

        $branch = DB::table('branches')->where('id', $branchId)->first();

        if (!$branch) {
            throw new \Exception("Branch {$branchId} not found");
        }

        // Bug fix 2: missing branch details is a warning, not a hard block
        if (empty($branch->unit_name) || empty($branch->address)) {
            logger()->warning("Branch {$branchId} missing unit_name or address — generation allowed but forms may show N/A");
        }

        $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // Attendance check: strict month/year match
        $attendanceExists = DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereNull('deleted_at')
            ->exists();

        if (!$attendanceExists) {
            throw new \Exception(
                "No attendance data found for tenant {$tenantId}. Upload attendance CSV first."
            );
        }

        // Payroll cycle check: strict month/year match
        $cycleExists = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->exists();

        if (!$cycleExists) {
            throw new \Exception(
                "No payroll cycle found for tenant {$tenantId}. Upload payroll CSV first."
            );
        }

        $payrollExists = DB::table('workforce_payroll_entry')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->exists();

        if (!$payrollExists) {
            throw new \Exception(
                "No payroll entries found for tenant {$tenantId}. Upload payroll CSV first."
            );
        }
    }
}
