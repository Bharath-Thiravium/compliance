<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXXIIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XXII');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        // Query advance records from database
        $rows = DB::table('workforce_advances as a')
            ->join('workforce_employee as e', 'e.id', '=', 'a.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('a.advance_date', [$startDate, $endDate])
            ->select([
                'e.name',
                DB::raw("COALESCE(e.father_name, '') as father_name"),
                DB::raw("COALESCE(e.designation, '') as designation"),
                DB::raw("COALESCE(a.advance_date, '') as advance_date_amount_1"),
                DB::raw("COALESCE(a.advance_date, '') as advance_date_amount_2"),
                DB::raw("'' as purpose"),
                DB::raw("COALESCE(a.num_instalments, '') as installments"),
                DB::raw("COALESCE(a.advance_date, '') as installment_repaid"),
                DB::raw("COALESCE(a.last_month, '') as last_installment_date"),
                DB::raw("'' as signature"),
            ])
            ->orderBy('a.advance_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XXII', $rows);

        $contractor = DB::table('contractor_master')
            ->where('tenant_id', $tenantId)
            ->first();
        $branch = DB::table('branches')->where('id', $branchId)->where('tenant_id', $tenantId)->first();
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        return [
            'contractor_name' => $contractor?->company_name ?? 'N/A',
            'work_nature' => $branch?->address ?? 'N/A',
            'establishment_name' => $branch?->branch_name ?? $branch?->unit_name ?? 'N/A',
            'principal_employer' => $tenant?->name ?? $tenant?->establishment_name ?? 'N/A',
            'month_year' => date('F Y', strtotime("$year-$month-01")),
            'rows' => $rows,
            'is_nil' => empty($rows),
            'totals' => []
        ];
    }
}
