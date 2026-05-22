<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class ShopsFormVIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('SHOPS_FORM_VI');

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
            ->where('a.status', 'holiday')
            ->select([
                'e.name as employee_name',
                'a.attendance_date',
                'a.status',
            ])
            ->orderBy('a.attendance_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('SHOPS_FORM_VI', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_holidays' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
