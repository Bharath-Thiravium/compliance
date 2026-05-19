<?php

namespace App\Compliance\Builders;

class DeductionRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $deductions = $this->deductionRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year)
            ->filter(fn($d) => $d->total_deductions > 0);

        if ($deductions->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $deductions->map(fn($ded) => [
            'employee_code' => $ded->employee->employee_code ?? 'N/A',
            'employee_name' => $ded->employee->name ?? 'N/A',
            'designation' => $ded->employee->designation ?? 'N/A',
            'pf_employee' => $ded->pf_employee ?? 0,
            'esi_employee' => $ded->esi_employee ?? 0,
            'advances' => $ded->advances ?? 0,
            'fines' => $ded->fines ?? 0,
            'total_deductions' => $ded->total_deductions ?? 0,
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_deductions' => $deductions->sum('total_deductions'),
        ];
    }
}
