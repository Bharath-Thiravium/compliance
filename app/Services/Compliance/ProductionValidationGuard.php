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

        // Allow MINIMAL subscription in development mode
        if (!app()->isProduction() && $user->tenant->subscription_type === 'MINIMAL') {
            // Development mode - allow MINIMAL subscription
        } elseif ($user->tenant->subscription_type !== 'FULL') {
            throw new \Exception(
                "Form generation requires FULL subscription. " .
                "Current subscription: {$user->tenant->subscription_type}"
            );
        }

        $branch = DB::table('branches')->where('id', $branchId)->first();
        
        if (!$branch) {
            throw new \Exception("Branch {$branchId} not found");
        }

        if (empty($branch->unit_name) || empty($branch->address)) {
            throw new \Exception(
                "Branch details incomplete for branch {$branchId}. " .
                "Configure unit name and address at /compliance/settings"
            );
        }

        $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $attendanceExists = DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->exists();

        if (!$attendanceExists) {
            throw new \Exception(
                "No attendance data found for {$periodStart->format('F Y')}. " .
                "Attendance is required to process payroll and generate forms."
            );
        }

        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->where('period_from', $periodStart)
            ->where('period_to', $periodEnd)
            ->value('id');

        if (!$cycleId) {
            throw new \Exception(
                "Payroll not processed for {$periodStart->format('F Y')}. " .
                "Run: php artisan compliance:process-payroll {$tenantId} {$branchId} {$month} {$year}"
            );
        }

        $payrollExists = DB::table('workforce_payroll_entry')
            ->where('payroll_cycle_id', $cycleId)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$payrollExists) {
            throw new \Exception(
                "No payroll entries found for {$periodStart->format('F Y')}. " .
                "Run: php artisan compliance:process-payroll {$tenantId} {$branchId} {$month} {$year}"
            );
        }
    }
}
