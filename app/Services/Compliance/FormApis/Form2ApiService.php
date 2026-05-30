<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form2ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        $records = DB::table('workforce_attendance as a')
            ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.branch_id', $branchId)
            ->whereBetween('a.attendance_date', [
                $this->periodStart->toDateString(),
                $this->periodEnd->toDateString(),
            ])
            ->select([
                'e.employee_code',
                'e.gender',
                DB::raw("COALESCE(a.shift_name, e.shift_name, '') as shift_name"),
                DB::raw("COALESCE(a.in_time, '') as shift_start"),
                DB::raw("COALESCE(a.out_time, '') as shift_end"),
                'a.attendance_date',
                DB::raw("COALESCE(a.weekly_off, 0) as weekly_off"),
                DB::raw("COALESCE(a.holiday_flag, 0) as holiday_flag"),
                'a.status',
            ])
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $place = '';
        if ($branch && !empty($branch->address)) {
            $parts = explode(',', $branch->address);
            $place = trim($parts[0]);
        }

        return [
            'factory_details' => [
                'factory_name'         => $branch->unit_name ?? $branch->branch_name ?? '',
                'place'                => $place,
                'district'             => $branch->district ?? '',
                'date_first_exhibited' => $this->periodStart->format('Y-m-d'),
            ],
            'records' => $records,
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
