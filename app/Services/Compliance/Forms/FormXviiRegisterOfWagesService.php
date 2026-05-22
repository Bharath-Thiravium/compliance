<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXviiRegisterOfWagesService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM XVII REGISTER OF WAGES');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.name as name',
                'e.employee_code as employee_code',
                'e.designation as designation',
                'e.days_worked as days_worked',
                'e.unit_work as unit_work',
                'e.daily_rate as daily_rate',
                'e.basic_wages as basic_wages',
                'e.da as da',
                'e.overtime as overtime',
                'e.other_cash as other_cash',
                'e.gross_salary as gross_salary',
                'e.esi as esi',
                'e.pf as pf',
                'e.pt as pt',
                'e.total_deductions as total_deductions',
                'e.net_amount as net_amount',
            ])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM XVII REGISTER OF WAGES', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'N/A',
            ],
            'period' => date('F Y', strtotime("$year-$month-01")),
        ];

        if (empty($rows)) {
            return [
                'header' => $header,
                'rows' => [],
                'is_nil' => true,
                'totals' => []
            ];
        }

        return [
            'header' => $header,
            'rows' => $rows,
            'is_nil' => false,
            'totals' => []
        ];
    }
}