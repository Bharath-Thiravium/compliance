<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form7Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_7');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        $rows = [];

        FormDebugger::end('FORM_7', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'N/A',
            ],
            'period' => date('F Y', strtotime("$year-$month-01")),
        ];

        return [
            'header' => $header,
            'rows' => $rows,
            'is_nil' => true,
            'totals' => []
        ];
    }
}
