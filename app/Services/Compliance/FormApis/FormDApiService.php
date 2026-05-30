<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormDApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Flat query — one row per employee per date.
        // Include soft-deleted employees who had attendance in this period
        // (they were active during the period; deleted_at is a later event).
        // Filter by attendance.tenant_id + branch_id directly to avoid missing
        // employees whose employee record tenant/branch differs from attendance record.
        $rows = DB::table('workforce_attendance as wa')
            ->join('workforce_employee as we', 'we.id', '=', 'wa.employee_id')
            ->where('wa.tenant_id', $tenantId)
            ->where('wa.branch_id', $branchId)
            ->whereYear('wa.attendance_date', $year)
            ->whereMonth('wa.attendance_date', $month)
            ->whereNull('wa.deleted_at')
            ->select([
                'we.id as employee_id',
                'we.employee_code',
                'we.name',
                'we.designation',
                DB::raw('DATE(wa.attendance_date) as attendance_date'),
                'wa.status',
                'wa.weekly_off',
                'wa.holiday_flag',
                'wa.leave_type',
            ])
            ->orderBy('we.employee_code')
            ->orderBy('wa.attendance_date')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        Log::info('FORM D RAW ATTENDANCE', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month'     => $month,
            'year'      => $year,
            'row_count' => count($rows),
            'sample'    => array_slice($rows, 0, 3),
        ]);

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
