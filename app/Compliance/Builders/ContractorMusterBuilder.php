<?php

namespace App\Compliance\Builders;

class ContractorMusterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $deployments->map(fn($dep) => [
            'employee_name' => $dep->employee->name ?? 'N/A',
            'contractor_name' => $dep->contractor->name ?? 'N/A',
            'deployment_date' => $dep->deployment_start ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
