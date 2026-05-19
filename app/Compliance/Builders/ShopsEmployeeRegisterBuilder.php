<?php

namespace App\Compliance\Builders;

class ShopsEmployeeRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $employees = $this->employeeRepo->getByBranch($this->tenantId, $this->branchId);
        
        if ($employees->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $employees->map(fn($emp) => [
            'employee_code' => $emp->employee_code ?? 'N/A',
            'employee_name' => $emp->name ?? 'N/A',
            'designation' => $emp->designation ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
