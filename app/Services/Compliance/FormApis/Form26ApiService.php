<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form26ApiService extends BaseFormApiService
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
            ->whereMonth('i.incident_date', $month)
            ->select([
                'i.id',
                'i.incident_date',
                'i.location',
                'i.description',
                'i.cause',
                'i.injury_type',
                'i.severity',
                'i.corrective_action',
                'e.name as employee_name',
                'e.designation',
                'e.employee_code',
                'e.gender',
                'e.esi_number',
            ])
            ->orderBy('i.incident_date')
            ->get()
            // Composite dedup: same incident_date + employee + injury_type = same incident
            ->unique(fn($r) => implode('|', [
                $r->incident_date  ?? '',
                $r->employee_code  ?? $r->employee_name ?? '',
                $r->injury_type    ?? '',
            ]))
            ->values()
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
