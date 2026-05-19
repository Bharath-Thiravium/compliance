<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form25Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_25');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.name as employee_name',
                DB::raw("'' as father_name"),
                'e.designation',
                '''',
                DB::raw("'' as place_of_employment"),
                DB::raw("'' as worker_group"),
                DB::raw("'' as relay"),
                DB::raw("'' as periods_of_work"),
                DB::raw("'' as date"),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_25', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_records' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
