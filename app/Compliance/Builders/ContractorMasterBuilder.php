<?php

namespace App\Compliance\Builders;

class ContractorMasterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $contractors = $this->contractorRepo->getContractors($this->tenantId);
        
        if ($contractors->isEmpty()) {
            return ['status' => 'NIL'];
        }

        return [
            'entries' => $contractors->map(fn($contractor) => [
                'contractor_name' => $contractor->name ?? 'N/A',
                'address' => $contractor->address ?? 'N/A',
                'license_number' => $contractor->license_number ?? 'N/A',
            ])->toArray(),
        ];
    }
}
