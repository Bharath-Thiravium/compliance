<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class EsiForm12Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('ESI_FORM_12');

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
                'e.esi_number',
                'pe.gross_salary',
                DB::raw('COALESCE(pe.esi_employee, 0) as esi_employee'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('ESI_FORM_12', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_employees' => count($rows),
            'total_esi_contribution' => array_sum(array_column($rows, 'esi_employee')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
