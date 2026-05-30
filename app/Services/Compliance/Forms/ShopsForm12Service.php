<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class ShopsForm12Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('SHOPS_FORM_12');


        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        // Use advances dataset for SHOPS FORM 12 (register of advances)
        $rows = DB::table('workforce_advances as wa')
            ->join('workforce_employee as e', 'e.id', '=', 'wa.employee_id')
            ->where('wa.tenant_id', $tenantId)
            ->where('wa.branch_id', $branchId)
            ->whereYear('wa.advance_date', $year)
            ->whereMonth('wa.advance_date', $month)
            ->whereNull('wa.deleted_at')
            ->select([
                'e.name as employee_name',
                'e.father_name',
                'wa.amount as advance_amount',
                'wa.advance_date',
                'wa.purpose as advance_purpose',
                'wa.num_instalments as advance_installments',
                DB::raw('NULL as advance_postponements'),
                DB::raw('NULL as advance_repaid_date'),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        // Fallback: payroll_entry advances if no workforce_advances exist
        if (empty($rows)) {
            $rows = DB::table('workforce_payroll_entry as pe')
                ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
                ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
                ->where('pe.tenant_id', $tenantId)
                ->where('pe.branch_id', $branchId)
                ->whereYear('pc.period_from', $year)
                ->whereMonth('pc.period_from', $month)
                ->where('pe.advances', '>', 0)
                ->select([
                    'e.name as employee_name',
                    'e.father_name',
                    'pe.advances as advance_amount',
                    'pc.period_from as advance_date',
                    DB::raw('"Salary Advance" as advance_purpose'),
                    DB::raw('1 as advance_installments'),
                    DB::raw('NULL as advance_postponements'),
                    DB::raw('NULL as advance_repaid_date'),
                ])
                ->orderBy('e.employee_code')
                ->get()
                ->map(fn($row) => (array)$row)
                ->toArray();
        }


        FormDebugger::end('SHOPS_FORM_12', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_employees' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
