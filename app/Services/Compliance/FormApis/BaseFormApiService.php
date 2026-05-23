<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

abstract class BaseFormApiService
{
    protected int $tenantId;
    protected int $branchId;
    protected int $month;
    protected int $year;
    protected Carbon $periodStart;
    protected Carbon $periodEnd;

    public function __construct()
    {
    }

    /**
     * Fetch and return structured data for form
     * 
     * Standard response structure:
     * [
     *   'records' => [...],
     *   'meta' => [
     *     'tenant_id' => int,
     *     'branch_id' => int,
     *     'month' => int,
     *     'year' => int
     *   ]
     * ]
     */
    abstract public function fetch(int $tenantId, int $branchId, int $month, int $year): array;

    /**
     * Initialize period dates
     */
    protected function initializePeriod(int $month, int $year): void
    {
        $this->month = $month;
        $this->year = $year;
        $this->periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $this->periodEnd = $this->periodStart->copy()->endOfMonth();
    }

    /**
     * Get tenant details
     */
    protected function getTenantDetails(int $tenantId): array
    {
        $tenant = DB::table('tenants')
            ->where('id', $tenantId)
            ->first();

        if (!$tenant) {
            return [
                'name' => 'N/A',
                'establishment_name' => 'N/A',
                'factory_license_no' => '',
                'pf_code' => '',
                'esi_code' => '',
            ];
        }

        return [
            'name' => $tenant->name ?? 'N/A',
            'owner_name' => $tenant->owner_name ?? $tenant->contact_person ?? 'N/A',
            'establishment_name' => !empty($tenant->establishment_name) ? $tenant->establishment_name : null,
            'factory_license_no' => $tenant->factory_license_no ?? '',
            'pf_code' => $tenant->pf_code ?? '',
            'esi_code' => $tenant->esi_code ?? '',
        ];
    }

    /**
     * Get branch details
     */
    protected function getBranchDetails(int $branchId, int $tenantId): array
    {
        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$branch) {
            return [
                'name' => 'N/A',
                'address' => 'N/A',
                'pf_code' => '',
                'esi_code' => '',
            ];
        }

        return [
            'name' => $branch->unit_name ?? $branch->branch_name ?? 'N/A',
            'address' => $branch->address ?? 'N/A',
            'pf_code' => $branch->pf_code ?? '',
            'esi_code' => $branch->esi_code ?? '',
        ];
    }

    /**
     * Format period
     */
    protected function formatPeriod(): string
    {
        return $this->periodStart->format('F Y');
    }

    /**
     * Validate tenant and branch exist
     */
    protected function validateTenantAndBranch(int $tenantId, int $branchId): void
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->exists();
        if (!$tenant) {
            throw new \Exception("Tenant {$tenantId} not found");
        }

        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$branch) {
            throw new \Exception("Branch {$branchId} not found for tenant {$tenantId}");
        }
    }
}
