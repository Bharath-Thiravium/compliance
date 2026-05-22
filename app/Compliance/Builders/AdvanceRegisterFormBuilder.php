<?php

namespace App\Compliance\Builders;

use App\Services\Compliance\Forms\FormXXIIService;

class AdvanceRegisterFormBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $service = new FormXXIIService();
        $data = $service->generate($this->tenantId, $this->branchId, $this->month, $this->year);
        
        return [
            'contractor_name' => $data['contractor_name'] ?? 'N/A',
            'work_nature' => $data['work_nature'] ?? 'N/A',
            'establishment_name' => $data['establishment_name'] ?? 'N/A',
            'principal_employer' => $data['principal_employer'] ?? 'N/A',
            'month_year' => $data['month_year'] ?? '',
            'rows' => $data['rows'] ?? [],
            'entries' => $data['rows'] ?? [],
            'is_nil' => $data['is_nil'] ?? true,
            'totals' => $data['totals'] ?? [],
            'period' => "{$this->month}/{$this->year}",
        ];
    }
}
