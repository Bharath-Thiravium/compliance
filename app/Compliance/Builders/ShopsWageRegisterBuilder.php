<?php

namespace App\Compliance\Builders;

class ShopsWageRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $entries = $this->payrollRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($entries->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $entries->map(fn($entry) => [
            'employee_name' => $entry->employee->name ?? 'N/A',
            'gross_salary' => $entry->gross_salary ?? 0,
            'deductions' => $entry->total_deductions ?? 0,
            'net_salary' => $entry->net_salary ?? 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_gross' => $entries->sum('gross_salary'),
        ];
    }
}
