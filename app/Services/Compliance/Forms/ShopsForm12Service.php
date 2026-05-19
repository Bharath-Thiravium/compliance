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

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.name as employee_name',
                'e.designation',
                'e.date_of_joining',
                'e.status',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

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
