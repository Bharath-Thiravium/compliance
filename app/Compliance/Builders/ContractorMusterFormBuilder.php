<?php

namespace App\Compliance\Builders;

use App\Services\Compliance\Forms\FormXVIService;

class ContractorMusterFormBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $service = new FormXVIService();
        $data = $service->generate($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if (empty($data['rows'])) {
            return ['status' => 'NIL'];
        }

        return [
            'contractor_name' => $data['contractor_name'] ?? 'N/A',
            'establishment_name' => $data['establishment_name'] ?? 'N/A',
            'principal_employer' => $data['principal_employer'] ?? 'N/A',
            'work_nature' => $data['work_nature'] ?? 'N/A',
            'work_location' => $data['work_location'] ?? 'N/A',
            'wage_period' => $data['wage_period'] ?? 'Monthly',
            'header' => $data['header'] ?? [],
            'rows' => $data['rows'] ?? [],
            'entries' => $data['rows'] ?? [],
            'totals' => $data['totals'] ?? [],
            'period' => "{$this->month}/{$this->year}",
        ];
    }
}
