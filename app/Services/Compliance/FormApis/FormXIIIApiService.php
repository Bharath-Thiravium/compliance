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

        try {
            // Query contract_labour_deployment (the actual table that exists)
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

            Log::info('FORM XIII: Fetched from contract_labour_deployment', ['count' => count($rows)]);

        } catch (\Exception $e) {
            Log::error('FORM XIII: Query failed', ['error' => $e->getMessage()]);
            $rows = [];
        }

        Log::info('FORM XIII: Final result', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'total_records' => count($rows),
        ]);

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
