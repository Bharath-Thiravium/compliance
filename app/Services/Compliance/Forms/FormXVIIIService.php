<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXVIIIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XVIII');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('workforce_attendance as a')
            ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('a.attendance_date', [$startDate, $endDate])
            ->select([
                'e.name as worker_name',
                DB::raw("'' as deduction_type"),
                DB::raw("0 as deduction_amount"),
                'a.attendance_date as deduction_date',
                DB::raw("'' as reason"),
            ])
            ->orderBy('a.attendance_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XVIII', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_deductions' => array_sum(array_column($rows, 'deduction_amount')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
