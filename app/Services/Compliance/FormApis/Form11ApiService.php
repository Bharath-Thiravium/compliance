<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form11ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('incidents as i')
            ->join('workforce_employee as e', 'e.id', '=', 'i.employee_id')
            ->where('i.tenant_id', $tenantId)
            ->where('i.branch_id', $branchId)
            ->whereYear('i.incident_date', $year)
            ->whereMonth('i.incident_date', $month)
            ->select([
                'i.id',
                'i.notice_date',
                'i.notice_time',
                'i.incident_date',
                'i.incident_time',
                'i.location',
                'i.cause',
                'i.injury_type',
                'i.activity',
                'i.first_aid_by',
                'i.witness',
                'i.remarks',
                'e.name',
                'e.permanent_address as address',
                'e.gender',
                DB::raw('TIMESTAMPDIFF(YEAR, e.date_of_birth, CURDATE()) as age'),
                'e.esi_number',
                'e.designation',
            ])
            ->orderBy('i.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
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
