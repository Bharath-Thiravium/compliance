<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form25ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $periodStart = $this->periodStart;
        $periodEnd   = $this->periodEnd;

        $employees = DB::table('workforce_employee as we')
            ->where('we.tenant_id', $tenantId)
            ->where('we.branch_id', $branchId)
            ->where('we.status', 'active')
            ->whereNull('we.deleted_at')
            ->select([
                'we.id',
                'we.employee_code',
                'we.name',
                'we.father_name',
                'we.designation',
                'we.gender',
                'we.date_of_birth',
                'we.date_of_joining',
            ])
            ->orderBy('we.name')
            ->get();

        $attendanceSummary = DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->whereIn('employee_id', $employees->pluck('id'))
            ->select('employee_id', 'attendance_date', 'status')
            ->get()
            ->groupBy('employee_id');

        $branchDetails = $this->getBranchDetails($branchId, $tenantId);
        $records = [];

        foreach ($employees as $emp) {
            $emp        = (array) $emp;
            $attendance = $attendanceSummary[$emp['id']] ?? collect();

            $presentDays = $attendance->whereIn('status', ['present', 'leave'])->count();
            $absentDays  = $attendance->where('status', 'absent')->count();
            $firstDate   = $attendance->sortBy('attendance_date')->first()?->attendance_date ?? '';

            $records[] = [
                'employee_code'       => $emp['employee_code'],
                'name'                => $emp['name'],
                'father_name'         => $emp['father_name']     ?? '',
                'designation'         => $emp['designation']     ?? '',
                'gender'              => $emp['gender']          ?? '',
                'date_of_birth'       => $emp['date_of_birth']   ?? '',
                'date_of_joining'     => $emp['date_of_joining'] ?? '',
                'place_of_employment' => $branchDetails['address'] ?? '',
                'attendance_date'     => $firstDate,
                'present_days'        => $presentDays,
                'absent_days'         => $absentDays,
                'total_days'          => $presentDays + $absentDays,
            ];
        }

        return [
            'records' => $records,
            'meta'    => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $branchDetails,
            'period' => $this->formatPeriod(),
        ];
    }
}
