<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXXIIIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XXIII');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$startDate, $endDate])
            ->select([
                'e.name',
                DB::raw("COALESCE(e.father_name, '') as father_name"),
                DB::raw("COALESCE(e.gender, '') as sex"),
                DB::raw("COALESCE(e.designation, '') as designation"),
                DB::raw("'' as overtime_dates"),
                DB::raw("COALESCE(cld.overtime_hours, 0) as total_overtime"),
                DB::raw("0 as normal_rate"),
                DB::raw("0 as overtime_rate"),
                DB::raw("0 as overtime_earnings"),
                DB::raw("'' as payment_date"),
                DB::raw("'' as remarks"),
            ])
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XXIII', $rows);

        $contractor = DB::table('contractor_master')
            ->where('tenant_id', $tenantId)
            ->first();
        $branch = DB::table('branches')->where('id', $branchId)->first();
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $header = [
            'contractor_name' => $contractor->company_name ?? 'N/A',
            'work_location' => $branch->address ?? 'N/A',
            'establishment_name' => $branch->branch_name ?? $branch->unit_name ?? 'N/A',
            'principal_employer' => $tenant->name ?? 'N/A',
            'month_year' => date('F Y', strtotime("$year-$month-01")),
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
