<?php

namespace App\Compliance\Builders;

class PrincipalAnnualBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $contractors = $this->contractorRepo->getContractors($this->tenantId);
        
        if ($contractors->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $contractors->map(fn($contractor) => [
            'contractor_name' => $contractor->name ?? 'N/A',
            'total_workmen' => 0,
            'total_wages' => 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
