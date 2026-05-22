<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form10Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_10');

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
                'e.designation',

                DB::raw('COALESCE(pe.overtime_hours,0) as overtime_hours'),

                DB::raw('COALESCE(pe.basic_earned / 8,0) as normal_rate'),

                DB::raw('COALESCE((pe.basic_earned / 8) * 1.5,0) as overtime_rate'),

                DB::raw('COALESCE(pe.basic_earned,0) as normal_earnings'),

                DB::raw('COALESCE(pe.overtime_wages,0) as overtime_wages'),

                DB::raw('0 as food_grain_benefit'),

                DB::raw('0 as is_piece_worker')
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_10', $rows);

        \Illuminate\Support\Facades\Log::info('FORM_10 service', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period'    => "{$month}/{$year}",
            'rows_raw'  => count($rows),
        ]);

        $rows = array_values(array_filter($rows, fn($r) => ($r['overtime_hours'] ?? 0) > 0));

        \Illuminate\Support\Facades\Log::info('FORM_10 after OT filter', ['rows_filtered' => count($rows)]);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_overtime_hours' => array_sum(array_column($rows, 'overtime_hours')),
            'total_overtime_wages' => array_sum(array_column($rows, 'overtime_wages')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
