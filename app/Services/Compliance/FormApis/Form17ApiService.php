<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

/**
 * Form17ApiService - Health Register API
 * 
 * Fetches worker health register data for FORM 17
 * (Health Register for persons employed in dangerous operations)
 * 
 * Returns worker employment details with all required statutory fields
 */
class Form17ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fetch worker health register data
        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->select([
                'e.id',
                'e.employee_code as works_no',
                'e.name as name_of_worker',
                'e.gender as sex',
                'e.date_of_birth',
                'e.date_of_joining',
                'e.designation',
                'e.department',
            ])
            ->orderBy('e.employee_code')
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
