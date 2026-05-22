<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormAApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->whereNull('e.deleted_at')
            ->select([
                'e.id',
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.gender',
                'e.date_of_birth',
                'e.marital_status',
                'e.nationality',
                'e.mobile',
                'e.email',
                'e.permanent_address',
                DB::raw('COALESCE(e.local_address, e.permanent_address) as present_address'),
                'e.designation',
                'e.department',
                'e.skill_type',
                'e.date_of_joining',
                'e.date_of_exit',
                'e.pf_number',
                'e.esi_number',
                DB::raw('COALESCE(e.uan_number, e.pf_number) as uan_number'),
                'e.pan',
                'e.aadhaar',
                'e.bank_account',
                'e.bank_name',
                'e.ifsc',
                'e.basic_salary',
                'e.status',
            ])
            ->orderBy('e.employee_code')
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
