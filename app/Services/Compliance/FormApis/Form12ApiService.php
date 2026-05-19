<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form12ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->select([
                'employee_code',
                'name',
                'father_name',
                'gender',
                'date_of_birth',
                'permanent_address as address',
                'designation',
                'department',
                'date_of_joining',
                'date_of_exit',
                'pf_number',
                'esi_number',
                DB::raw('COALESCE(uan_number, pf_number) as uan'),
                'mobile',
                'bank_account',
                'bank_name',
                'ifsc',
                'skill_type',
                'nationality',
            ])
            ->orderBy('employee_code')
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
