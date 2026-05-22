<?php

namespace App\Compliance\Builders;

class ShopsUnpaidBonusBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $unpaid = $this->bonusRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year)
            ->filter(fn($b) => $b->status === 'unpaid');
        
        if ($unpaid->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $unpaid->map(fn($bonus) => [
            'employee_name' => $bonus->employee->name ?? 'N/A',
            'bonus_amount' => $bonus->bonus_amount ?? 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_unpaid' => $unpaid->sum('bonus_amount'),
        ];
    }
}
