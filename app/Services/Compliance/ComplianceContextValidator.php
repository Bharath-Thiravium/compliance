<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;

class ComplianceContextValidator
{
    public static function validate(int $tenantId, int $branchId, int $month, int $year): void
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        if (!$tenant) {
            throw new \RuntimeException("Tenant {$tenantId} not found");
        }

        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$branch) {
            throw new \RuntimeException("Branch {$branchId} not found or does not belong to tenant {$tenantId}");
        }

        if ($month < 1 || $month > 12) {
            throw new \RuntimeException("Invalid month: {$month}");
        }

        if ($year < 2020 || $year > 2030) {
            throw new \RuntimeException("Invalid year: {$year}");
        }

        $name = $tenant->establishment_name ?? $tenant->name;
        if (empty($name)) {
            throw new \RuntimeException("Tenant {$tenantId} missing establishment name. Configure in Settings.");
        }
    }

    public static function validatePayrollExists(int $tenantId, int $branchId, int $month, int $year): void
    {
        $count = DB::table('workforce_payroll_entry')
            ->where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->count();

        if ($count === 0) {
            throw new \RuntimeException(
                "No payroll data found for {$month}/{$year}. Run: php artisan compliance:process-payroll {$tenantId} {$branchId} {$month} {$year}"
            );
        }
    }

    public static function resolveBranchSafe(int $tenantId, ?int $branchId): int
    {
        if ($branchId) {
            $branch = DB::table('branches')
                ->where('id', $branchId)
                ->where('tenant_id', $tenantId)
                ->first();

            if ($branch) {
                return $branchId;
            }
        }

        $branch = DB::table('branches')
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$branch) {
            throw new \RuntimeException("No branches found for tenant {$tenantId}");
        }

        return $branch->id;
    }
}
