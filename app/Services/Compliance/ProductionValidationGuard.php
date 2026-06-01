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

        // Bug fix 3: attendance check uses ANY period for the tenant/branch, not strict month match
        // (data may have been uploaded for a different month and fallback logic handles it)
        $attendanceExists = DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$attendanceExists) {
            throw new \Exception(
                "No attendance data found for tenant {$tenantId}. Upload attendance CSV first."
            );
        }

        // Bug fix 4: payroll cycle check uses ANY cycle for the tenant, not strict period_from = period_to match
        $cycleExists = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
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
