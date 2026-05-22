<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ESIForm12ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('incidents as i')
            ->leftJoin('workforce_employee as e', 'i.employee_id', '=', 'e.id') // fixed: was i.id = e.id
            ->where('i.tenant_id', $tenantId)
            ->where('i.branch_id', $branchId)
            ->whereYear('i.incident_date', $year)
            ->whereMonth('i.incident_date', $month)
            ->select([
                'i.id',
                'i.incident_date',
                'i.description',
                'i.severity',
                'i.location',
                'i.cause',
                'i.injury_type',
                'e.name as employee_name',
                'e.father_name',
                'e.esi_number as insurance_no',
                'e.designation as occupation',
                'e.department',
                'e.gender',
                'e.permanent_address as address',
                DB::raw('TIMESTAMPDIFF(YEAR, e.date_of_birth, CURDATE()) as age'),
            ])
            ->orderBy('i.incident_date')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        return [
            'records' => $rows,
            'meta'    => [
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
