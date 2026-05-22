<?php

namespace App\Compliance\Repositories;

use App\Models\WorkforceAttendance;
use Illuminate\Support\Collection;

class AttendanceRepository
{
    public function getByPeriod(int $tenantId, int $month, int $year): Collection
    {
        return WorkforceAttendance::where('tenant_id', $tenantId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->with('employee')
            ->get();
    }

    public function getByBranchAndPeriod(int $tenantId, int $branchId, int $month, int $year): Collection
    {
        return WorkforceAttendance::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->with('employee')
            ->get();
    }

    public function getByEmployee(int $employeeId, int $month, int $year): Collection
    {
        return WorkforceAttendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get();
    }

    public function getDaysWorked(int $employeeId, int $month, int $year): int
    {
        return WorkforceAttendance::where('employee_id', $employeeId)
            ->where('status', 'present')
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->count();
    }
}
