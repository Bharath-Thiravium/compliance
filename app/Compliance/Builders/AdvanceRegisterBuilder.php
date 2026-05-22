<?php

namespace App\Compliance\Builders;

class AdvanceRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $advances = $this->deductionRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year)
            ->filter(fn($a) => $a->advances > 0);
        
        if ($advances->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $advances->map(fn($adv) => [
            'employee_name' => $adv->employee->name ?? 'N/A',
            'advance_amount' => $adv->advances ?? 0,
            'date' => $adv->payment_date ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_advances' => $advances->sum('advances'),
        ];
    }
}
