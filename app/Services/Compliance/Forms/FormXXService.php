<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXXService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XX');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        // Query deduction records (not attendance!)
        $rows = DB::table('workforce_deductions as d')
            ->join('workforce_employee as e', 'e.id', '=', 'd.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('d.deduction_date', [$startDate, $endDate])
            ->select([
                'e.name as employee_name',
                DB::raw("COALESCE(e.father_name,'') as father_name"),
                DB::raw("COALESCE(e.designation,'') as designation"),
                DB::raw("COALESCE(d.particulars, '') as damage_particulars"),
                DB::raw("COALESCE(d.deduction_date, '') as damage_date"),
                DB::raw("COALESCE(CASE WHEN d.showed_cause = 1 THEN 'Yes' ELSE 'No' END, '') as showed_cause"),
                DB::raw("COALESCE(d.witness_name, '') as witness_name"),
                DB::raw("COALESCE(d.amount, 0) as deduction_amount"),
                DB::raw("COALESCE(d.num_instalments, '') as instalments"),
                DB::raw("COALESCE(d.first_month, '') as first_month"),
                DB::raw("COALESCE(d.last_month, '') as last_month"),
                DB::raw("COALESCE(d.remarks, '') as remarks")
            ])
            ->orderBy('d.deduction_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XX', $rows);

        /*
        HEADER DATA
        */

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branch = DB::table('branches')->where('id', $branchId)->where('tenant_id', $tenantId)->first();
        $contractor = DB::table('contractor_master')
            ->where('tenant_id', $tenantId)
            ->first();

        $header = [
            'contractor_name' => $contractor?->company_name ?? 'N/A',
            'work_nature' => $branch?->address ?? 'N/A',
            'establishment_name' => $branch?->branch_name ?? $branch?->unit_name ?? 'N/A',
            'principal_employer' => $tenant?->name ?? $tenant?->establishment_name ?? 'N/A',
            'period' => date('F Y', strtotime("$year-$month-01")),
        ];

        if (empty($rows)) {
            return [
                'header' => $header,
                'rows' => [],
                'is_nil' => true,
                'totals' => []
            ];
        }

        return [
            'header' => $header,
            'rows' => $rows,
            'is_nil' => false,
            'totals' => []
        ];
    }
}
