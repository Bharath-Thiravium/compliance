<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form26AApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('incidents as i')
            ->leftJoin('workforce_employee as e', 'i.employee_id', '=', 'e.id')
            ->where('i.tenant_id', $tenantId)
            ->where('i.branch_id', $branchId)
            ->whereYear('i.incident_date', $year)
            ->select([
                'i.id',
                'i.incident_date',
                'i.incident_time',
                'i.description',
                'i.severity',
                'i.location',
                'i.cause',
                'i.remarks',
                'e.name as employee_name',
                'e.employee_code',
                'e.designation',
            ])
            ->orderBy('i.incident_date')
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
