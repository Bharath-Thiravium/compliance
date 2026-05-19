<?php

namespace App\Compliance\Builders;

class FinesRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $fines = $this->deductionRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year)
            ->filter(fn($f) => $f->fines > 0);
        
        if ($fines->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $fines->map(fn($fine) => [
            'employee_name' => $fine->employee->name ?? 'N/A',
            'fine_amount' => $fine->fines ?? 0,
            'reason' => 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_fines' => $fines->sum('fines'),
        ];
    }
}
