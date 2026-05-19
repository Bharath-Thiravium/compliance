<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class ShopsFormCService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('SHOPS_FORM_C');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('bonus_records as b')
            ->join('workforce_employee as e', 'e.id', '=', 'b.employee_id')
            ->where('b.tenant_id', $tenantId)
            ->where('b.branch_id', $branchId)
            ->whereBetween('b.payment_date', [$startDate, $endDate])
            ->select([
                'e.name as employee_name',
                'b.bonus_amount',
                'b.payment_date',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('SHOPS_FORM_C', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_bonus' => array_sum(array_column($rows, 'bonus_amount')),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
