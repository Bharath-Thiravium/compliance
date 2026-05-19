<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form26ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fetch accident records from incident_documents with employee details
        $rows = DB::table('incident_documents as id')
            ->leftJoin('workforce_employee as we', 'id.employee_id', '=', 'we.id')
            ->where('id.tenant_id', $tenantId)
            ->where('id.branch_id', $branchId)
            ->where('id.incident_type', 'accident')
            ->whereYear('id.incident_date', $year)
            ->whereMonth('id.incident_date', $month)
            ->select([
                'id.id',
                'id.incident_date',
                'id.location',
                'id.description',
                'we.name as employee_name',
                'we.designation',
                'we.employee_code',
            ])
            ->orderBy('id.incident_date')
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
