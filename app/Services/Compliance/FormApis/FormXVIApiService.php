<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXVIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_attendance as wa')
            ->join('workforce_employee as we', 'we.id', '=', 'wa.employee_id')
            ->where('we.tenant_id', $tenantId)
            ->where('we.branch_id', $branchId)
            ->whereNull('we.deleted_at')
            ->whereYear('wa.attendance_date', $year)
            ->whereMonth('wa.attendance_date', $month)
            ->whereNull('wa.deleted_at')
            ->select([
                'we.employee_code',
                'we.name',
                'we.father_name',
                'we.designation',
                'we.gender as sex',
                'wa.attendance_date',
                'wa.status',
            ])
            ->orderBy('we.employee_code')
            ->orderBy('wa.attendance_date')
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
