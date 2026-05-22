<?php

namespace App\Compliance\Builders;

class ContractorWorkmenBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deployments = $this->contractorRepo->getDeploymentsByBranch($this->tenantId, $this->branchId, $this->month, $this->year);

        if ($deployments->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $deployments->map(fn($dep) => [
            'worker_name' => $dep->employee->name ?? 'N/A',
            'contractor_name' => $dep->contractor->company_name ?? 'N/A',
            'deployment_start' => $dep->deployment_start ?? 'N/A',
            'deployment_end' => $dep->deployment_end ?? 'N/A',
            'wage_rate' => $dep->wage_rate ?? 0,
            'work_order' => $dep->work_order_number ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_workers' => $deployments->count(),
        ];
    }
}
