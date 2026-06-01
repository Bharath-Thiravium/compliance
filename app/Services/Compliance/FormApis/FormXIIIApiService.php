<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        Log::info('FORM XIII DIAGNOSTIC START', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
        ]);

        try {
            // DIAGNOSTIC 1: Check raw data in contract_labour_deployment
            $allDeployments = DB::table('contract_labour_deployment')
                ->where('tenant_id', $tenantId)
                ->get();
            
            Log::info('FORM XIII: All deployments for tenant', [
                'count' => count($allDeployments),
                'data' => $allDeployments->toArray(),
            ]);

            // DIAGNOSTIC 2: Check contractors
            $allContractors = DB::table('contractor_master')
                ->where('tenant_id', $tenantId)
                ->get();
            
            Log::info('FORM XIII: All contractors for tenant', [
                'count' => count($allContractors),
                'data' => $allContractors->toArray(),
            ]);

            // DIAGNOSTIC 3: Check employees
            $allEmployees = DB::table('workforce_employee')
                ->where('tenant_id', $tenantId)
                ->get();
            
            Log::info('FORM XIII: All employees for tenant', [
                'count' => count($allEmployees),
            ]);

            // DIAGNOSTIC 4: Try the join without filters
            $joinTest = DB::table('contract_labour_deployment as cld')
                ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
                ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
                ->where('cld.tenant_id', $tenantId)
                ->select([
                    'cld.id',
                    'cld.contractor_id',
                    'cld.employee_id',
                    'cld.branch_id',
                    'cld.deployment_start',
                    'cld.deployment_end',
                    'we.name as employee_name',
                    'cm.contractor_name',
                ])
                ->get();
            
            Log::info('FORM XIII: Join test result', [
                'count' => count($joinTest),
                'data' => $joinTest->toArray(),
            ]);

            // DIAGNOSTIC 5: Try with branch filter
            $withBranchFilter = DB::table('contract_labour_deployment as cld')
                ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
                ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
                ->where('cld.tenant_id', $tenantId)
                ->where(function ($query) use ($branchId) {
                    $query->where('cld.branch_id', $branchId)
                          ->orWhereNull('cld.branch_id');
                })
                ->select([
                    'cld.id',
                    'cld.contractor_id',
                    'cld.employee_id',
                    'cld.branch_id',
                    'cld.deployment_start',
                    'cld.deployment_end',
                    'we.name as employee_name',
                    'cm.contractor_name',
                ])
                ->get();
            
            Log::info('FORM XIII: With branch filter', [
                'count' => count($withBranchFilter),
                'data' => $withBranchFilter->toArray(),
            ]);

            // DIAGNOSTIC 6: Check deleted_at filters
            $withDeletedFilter = DB::table('contract_labour_deployment as cld')
                ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
                ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
                ->where('cld.tenant_id', $tenantId)
                ->where(function ($query) use ($branchId) {
                    $query->where('cld.branch_id', $branchId)
                          ->orWhereNull('cld.branch_id');
                })
                ->whereNull('cld.deleted_at')
                ->whereNull('we.deleted_at')
                ->whereNull('cm.deleted_at')
                ->select([
                    'cld.id',
                    'cld.contractor_id',
                    'cld.employee_id',
                    'cld.branch_id',
                    'cld.deployment_start',
                    'cld.deployment_end',
                    'we.name as employee_name',
                    'cm.contractor_name',
                ])
                ->get();
            
            Log::info('FORM XIII: With deleted_at filter', [
                'count' => count($withDeletedFilter),
                'data' => $withDeletedFilter->toArray(),
            ]);

            // Now do the actual query
            $rows = DB::table('contract_labour_deployment as cld')
                ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
                ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
                ->where('cld.tenant_id', $tenantId)
                ->where(function ($query) use ($branchId) {
                    $query->where('cld.branch_id', $branchId)
                          ->orWhereNull('cld.branch_id');
                })
                ->whereNull('cld.deleted_at')
                ->whereNull('we.deleted_at')
                ->whereNull('cm.deleted_at')
                ->select([
                    DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                    DB::raw("COALESCE(cm.address, cm.company_address, 'N/A') as contractor_address"),
                    'we.name',
                    'we.date_of_birth',
                    'we.gender',
                    'we.father_name',
                    'we.designation',
                    'we.permanent_address',
                    'we.local_address',
                    'cld.deployment_start as joining_date',
                    'cld.deployment_end as termination_date',
                ])
                ->orderBy('cld.deployment_start')
                ->get()
                ->map(fn($row) => (array)$row)
                ->toArray();

            Log::info('FORM XIII: Final query result', ['count' => count($rows)]);

        } catch (\Exception $e) {
            Log::error('FORM XIII: Query error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $rows = [];
        }

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $this->month,
                'year' => $this->year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
