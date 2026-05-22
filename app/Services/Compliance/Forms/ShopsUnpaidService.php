<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class ShopsUnpaidService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('SHOPS_UNPAID');

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
            ->where('a.status', 'absent')
            ->select([
                'e.name as employee_name',
                'a.attendance_date',
                DB::raw("0 as deduction_amount"),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('SHOPS_UNPAID', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_unpaid' => array_sum(array_column($rows, 'deduction_amount')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
