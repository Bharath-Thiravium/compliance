<?php

namespace App\Compliance\Builders;

use App\Services\Compliance\Forms\FormXXService;

class DeductionRegisterFormBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $service = new FormXXService();
        $data = $service->generate($this->tenantId, $this->branchId, $this->month, $this->year);
        
        return [
            'header' => $data['header'] ?? [],
            'rows' => $data['rows'] ?? [],
            'entries' => $data['rows'] ?? [],
            'is_nil' => $data['is_nil'] ?? true,
            'totals' => $data['totals'] ?? [],
            'period' => "{$this->month}/{$this->year}",
        ];
    }
}
