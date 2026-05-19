<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class EpfInspectionService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('EPF_INSPECTION');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('pc.period_from', [$startDate, $endDate])
            ->select([
                'e.name as employee_name',
                'e.pf_number',
                'pe.basic_earned as basic_salary',
                DB::raw('COALESCE(pe.pf_employee, 0) as pf_employee'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('EPF_INSPECTION', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_employees' => count($rows),
            'total_pf_contribution' => array_sum(array_column($rows, 'pf_employee')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
