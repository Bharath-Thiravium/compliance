<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXVIIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XVII');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->where('pe.tenant_id', $tenantId)
            ->where('pe.branch_id', $branchId)
            ->whereBetween('pe.period_start', [$startDate, $endDate])
            ->select([
                'e.name',
                'e.employee_code',
                'e.designation',
                DB::raw('COALESCE(pe.days_worked, 0) as days_worked'),
                DB::raw("'' as unit_work"),
                DB::raw('COALESCE(pe.daily_rate, 0) as daily_rate'),
                DB::raw('COALESCE(pe.basic_salary, 0) as basic_wages'),
                DB::raw('COALESCE(pe.dearness_allowance, 0) as da'),
                DB::raw('COALESCE(pe.overtime_amount, 0) as overtime'),
                DB::raw('COALESCE(pe.other_allowances, 0) as other_cash'),
                DB::raw('COALESCE(pe.gross_salary, 0) as gross_salary'),
                DB::raw('COALESCE(pe.esi_deduction, 0) as esi'),
                DB::raw('COALESCE(pe.pf_deduction, 0) as pf'),
                DB::raw('COALESCE(pe.pt_deduction, 0) as pt'),
                DB::raw('COALESCE(pe.total_deductions, 0) as total_deductions'),
                DB::raw('COALESCE(pe.net_salary, 0) as net_amount'),
            ])
            ->orderBy('e.name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XVII', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branch = DB::table('branches')->where('id', $branchId)->first();
        
        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'NIL',
                'address' => $tenant?->address ?? 'NIL',
            ],
            'branch' => [
                'name' => $branch?->branch_name ?? $branch?->unit_name ?? 'NIL',
                'address' => $branch?->address ?? 'NIL',
            ]
        ];

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_basic' => array_sum(array_column($rows, 'basic_wages')),
            'total_da' => array_sum(array_column($rows, 'da')),
            'total_overtime' => array_sum(array_column($rows, 'overtime')),
            'total_gross' => array_sum(array_column($rows, 'gross_salary')),
            'total_deductions' => array_sum(array_column($rows, 'total_deductions')),
            'total_net' => array_sum(array_column($rows, 'net_amount')),
        ];

        return [
            'header' => $header,
            'rows' => $rows,
            'totals' => $totals
        ];
    }
}
