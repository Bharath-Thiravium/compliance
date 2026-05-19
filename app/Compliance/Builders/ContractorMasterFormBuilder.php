<?php

namespace App\Compliance\Builders;

use App\Services\Compliance\Forms\FormXIIService;

class ContractorMasterFormBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $service = new FormXIIService();
        $data = $service->generate($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if (empty($data['rows'])) {
            return ['status' => 'NIL'];
        }

        return [
            'header' => $data['header'] ?? [],
            'rows' => $data['rows'] ?? [],
            'entries' => $data['rows'] ?? [],
            'totals' => $data['totals'] ?? [],
            'period' => "{$this->month}/{$this->year}",
        ];
    }
}
