<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsFormVIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fetch declared holidays for the year from the holidays table
        $holidays = DB::table('holidays')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->select(['holiday_date', 'holiday_name', 'holiday_type'])
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        // Fetch all active employees for the branch
        $employees = DB::table('workforce_employee as we')
            ->where('we.tenant_id', $tenantId)
            ->where('we.branch_id', $branchId)
            ->whereNull('we.deleted_at')
            ->select(['we.id as employee_id', 'we.employee_code', 'we.name', 'we.father_name', 'we.designation'])
            ->orderBy('we.employee_code')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $employeeIds = array_column($employees, 'employee_id');

        // If no holidays declared, fall back to attendance rows marked as holiday
        if (empty($holidays) && !empty($employeeIds)) {
            $holidays = DB::table('workforce_attendance as wa')
                ->whereIn('wa.employee_id', $employeeIds)
                ->where('wa.tenant_id', $tenantId)
                ->whereYear('wa.attendance_date', $year)
                ->where('wa.status', 'holiday')
                ->select([
                    DB::raw("DATE_FORMAT(wa.attendance_date, '%Y-%m-%d') as holiday_date"),
                    DB::raw("'Holiday' as holiday_name"),
                    DB::raw("'national' as holiday_type"),
                ])
                ->distinct()
                ->orderBy(DB::raw("DATE_FORMAT(wa.attendance_date, '%Y-%m-%d')"))
                ->get()
                ->map(fn($r) => (array) $r)
                ->toArray();
        }

        // Normalize holiday dates to Y-m-d strings for consistent matching
        foreach ($holidays as &$h) {
            $h['holiday_date'] = date('Y-m-d', strtotime($h['holiday_date']));
        }
        unset($h);

        $holidayDates = array_column($holidays, 'holiday_date');

        $attendanceOnHolidays = [];
        if (!empty($holidayDates) && !empty($employeeIds)) {
            $rows = DB::table('workforce_attendance as wa')
                ->join('workforce_employee as we', 'we.id', '=', 'wa.employee_id')
                ->where('wa.tenant_id', $tenantId)
                ->whereIn('wa.employee_id', $employeeIds)
                ->whereIn(DB::raw("DATE_FORMAT(wa.attendance_date, '%Y-%m-%d')"), $holidayDates)
                ->select(['we.employee_code', DB::raw("DATE_FORMAT(wa.attendance_date, '%Y-%m-%d') as attendance_date"), 'wa.status'])
                ->get();

            foreach ($rows as $row) {
                $attendanceOnHolidays[$row->employee_code][$row->attendance_date] = (array) $row;
            }
        }

        return [
            'holidays'  => $holidays,
            'employees' => $employees,
            'attendance_on_holidays' => $attendanceOnHolidays,
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
