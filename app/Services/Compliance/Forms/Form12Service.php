<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form12Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_12');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('''', '<=', now()->subYears(18))
            ->select([
                'e.name as employee_name',
                DB::raw("'' as father_name"),
                'e.designation',
                DB::raw("'' as worker_group"),
                DB::raw("'' as relay"),
                DB::raw("'' as certificate_no"),
                DB::raw("'' as token_no"),
                DB::raw("'' as remarks"),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_12', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_employees' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
