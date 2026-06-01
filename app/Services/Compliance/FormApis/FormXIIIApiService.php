<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fetch contractor details for this branch
        $contractor = DB::table('contractor_master as cm')
            ->where('cm.tenant_id', $tenantId)
            ->where('cm.branch_id', $branchId)
            ->whereNull('cm.deleted_at')
            ->select([
                DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                DB::raw("COALESCE(cm.address, cm.company_address, 'N/A') as contractor_address"),
            ])
            ->first();

        $contractorName = $contractor->contractor_name ?? 'N/A';
        $contractorAddress = $contractor->contractor_address ?? 'N/A';

        // Fetch workmen deployed under this contractor
        $rows = DB::table('contract_labour_deployment as cld')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereNull('cld.deleted_at')
            ->join('workforce_employee as we', 'cld.employee_id', '=', 'we.id')
            ->select([
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
            ->orderBy('we.name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
            'contractor_name' => $contractorName,
            'contractor_address' => $contractorAddress,
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
