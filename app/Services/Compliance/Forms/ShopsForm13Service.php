<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class ShopsForm13Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('SHOPS_FORM_13');

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
            ->where('a.status', 'leave')
            ->select([
                'e.name as employee_name',
                'a.attendance_date',
                'a.status',
            ])
            ->orderBy('a.attendance_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('SHOPS_FORM_13', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_leave_days' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
