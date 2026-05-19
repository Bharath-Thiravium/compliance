<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXviMusterRollService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM XVI MUSTER ROLL');

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
                'e.father_name as father_name',
                'e.sex as sex',
                'e.remarks as remarks',
            ])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM XVI MUSTER ROLL', $rows);

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