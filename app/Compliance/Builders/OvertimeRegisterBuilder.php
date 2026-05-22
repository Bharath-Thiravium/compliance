<?php

namespace App\Compliance\Builders;

class OvertimeRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $entries = $this->payrollRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year)
            ->filter(fn($e) => $e->overtime_hours > 0);

        if ($entries->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $entries->map(fn($entry) => [
            'employee_code' => $entry->employee->employee_code ?? 'N/A',
            'employee_name' => $entry->employee->name ?? 'N/A',
            'designation' => $entry->employee->designation ?? 'N/A',
            'overtime_hours' => $entry->overtime_hours ?? 0,
            'overtime_wages' => $entry->overtime_wages ?? 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_overtime_hours' => $entries->sum('overtime_hours'),
            'total_overtime_wages' => $entries->sum('overtime_wages'),
        ];
    }
}
