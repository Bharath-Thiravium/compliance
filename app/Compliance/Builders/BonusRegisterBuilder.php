<?php

namespace App\Compliance\Builders;

class BonusRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $bonuses = $this->bonusRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);

        if ($bonuses->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $bonuses->map(fn($bonus) => [
            'employee_code' => $bonus->employee->employee_code ?? 'N/A',
            'employee_name' => $bonus->employee->name ?? 'N/A',
            'designation' => $bonus->employee->designation ?? 'N/A',
            'bonus_amount' => $bonus->bonus_amount ?? 0,
            'payment_date' => $bonus->payment_date ?? 'N/A',
            'status' => $bonus->status ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_bonus' => $bonuses->sum('bonus_amount'),
        ];
    }
}
