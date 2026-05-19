<?php

namespace App\Compliance\Builders;

class AttendanceRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $records = $this->attendanceRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);

        if ($records->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $grouped = $records->groupBy('employee_id');

        $mapped = $grouped->map(fn($group) => [
            'employee_code' => $group->first()->employee->employee_code ?? 'N/A',
            'employee_name' => $group->first()->employee->name ?? 'N/A',
            'present_days' => $group->where('status', 'present')->count(),
            'absent_days' => $group->where('status', 'absent')->count(),
            'leave_days' => $group->where('status', 'leave')->count(),
            'total_days' => $group->count(),
        ])->values()->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_employees' => count($mapped),
        ];
    }
}
