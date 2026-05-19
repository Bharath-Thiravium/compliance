<?php

namespace App\Compliance\Builders;

class EmployeeRegisterBuilder extends BaseBuilder
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
            'date_of_joining' => $emp->date_of_joining ?? 'N/A',
            'gender' => $emp->gender ?? 'N/A',
            'date_of_birth' => $emp->date_of_birth ?? 'N/A',
            'category' => $emp->category ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_employees' => $employees->count(),
        ];
    }
}
