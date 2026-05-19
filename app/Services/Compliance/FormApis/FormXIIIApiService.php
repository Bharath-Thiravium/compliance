<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
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
            ->orderBy('cld.deployment_start')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
