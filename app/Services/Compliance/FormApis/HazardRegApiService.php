<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class HazardRegApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('hazard_register')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'hazard_date',
                'hazard_type',
                'description',
                'location',
                'severity',
                'risk_rating',
                'control_measure',
                'corrective_action',
                'preventive_action',
                'reported_by',
                'status',
            ])
            ->orderBy('hazard_date')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
