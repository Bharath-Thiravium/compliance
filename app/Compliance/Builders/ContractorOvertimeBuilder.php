<?php

namespace App\Compliance\Builders;

class ContractorOvertimeBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $deployments->map(fn($dep) => [
            'employee_name' => $dep->employee->name ?? 'N/A',
            'overtime_hours' => $dep->overtime_hours ?? 0,
            'overtime_wages' => $dep->overtime_wages ?? 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
