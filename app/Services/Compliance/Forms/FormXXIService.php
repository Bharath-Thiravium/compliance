<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXXIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XXI');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        // Query fine records from database
        $rows = DB::table('workforce_fines as f')
            ->join('workforce_employee as e', 'e.id', '=', 'f.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('f.fine_date', [$startDate, $endDate])
            ->select([
                'e.name',
                DB::raw("COALESCE(e.father_name, '') as father_name"),
                DB::raw("COALESCE(e.designation, '') as designation"),
                DB::raw("COALESCE(f.reason, '') as act_or_omission"),
                DB::raw("COALESCE(f.fine_date, '') as date_of_offence"),
                DB::raw("'Yes' as showed_cause"),
                DB::raw("'' as heard_by"),
                DB::raw("'' as wage_period"),
                DB::raw("COALESCE(f.amount, 0) as fine_amount"),
                DB::raw("COALESCE(f.fine_date, '') as fine_realised"),
                DB::raw("COALESCE(f.remarks, '') as remarks"),
            ])
            ->orderBy('f.fine_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XXI', $rows);

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
