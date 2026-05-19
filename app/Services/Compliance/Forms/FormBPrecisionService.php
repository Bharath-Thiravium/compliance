<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormBPrecisionService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM B PRECISION');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.total_days_worked as total_days_worked',
                'e.daily_rate as daily_rate',
                'e.hra_earned as hra_earned',
                'e.basic_earned as basic_earned',
                'e.da_earned as da_earned',
                'e.overtime_wages as overtime_wages',
                'e.pf_employee as pf_employee',
                'e.esi_employee as esi_employee',
                'e.advances as advances',
                'e.fines as fines',
                'e.employee_name as employee_name',
                'e.designation as designation',
                'e.net_salary as net_salary',
            ])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM B PRECISION', $rows);

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