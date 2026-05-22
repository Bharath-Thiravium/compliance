<?php

namespace App\Compliance\Builders;

class ContractorWageRegisterBuilder extends BaseBuilder
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
            'wage_amount' => $dep->wage_amount ?? 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
