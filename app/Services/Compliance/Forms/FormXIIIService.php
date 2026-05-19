<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;
use Carbon\Carbon;

class FormXIIIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XIII');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('contract_labour_deployment as cl')
            ->leftJoin('workforce_employee as e', 'e.id', '=', 'cl.employee_id')
            ->where('cl.tenant_id', $tenantId)
            ->where('cl.branch_id', $branchId)
            ->whereBetween('cl.deployment_start', [$startDate, $endDate])
            ->select([
                DB::raw("COALESCE(e.name, '') as name"),
                DB::raw("COALESCE(CAST((julianday('now') - julianday(e.date_of_birth)) / 365.25 AS INTEGER), '') as age"),
                DB::raw("COALESCE(e.gender, '') as sex"),
                DB::raw("COALESCE(e.father_name, '') as father_name"),
                DB::raw("COALESCE(e.designation, '') as designation"),
                DB::raw("COALESCE(e.permanent_address, '') as permanent_address"),
                DB::raw("COALESCE(e.local_address, '') as local_address"),
                DB::raw("COALESCE(cl.deployment_start, '') as joining_date"),
                DB::raw("COALESCE(cl.deployment_end, '') as termination_date"),
                DB::raw("COALESCE(cl.termination_reason, '') as termination_reason"),
                DB::raw("COALESCE(cl.remarks, '') as remarks")
            ])
            ->orderBy('cl.deployment_start')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XIII', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branch = DB::table('branches')->where('id', $branchId)->where('tenant_id', $tenantId)->first();

        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? $tenant?->establishment_name ?? 'NIL',
                'address' => $tenant?->address ?? 'NIL',
            ],
            'branch' => [
                'name' => $branch?->branch_name ?? $branch?->unit_name ?? 'NIL',
                'address' => $branch?->address ?? 'NIL',
            ]
        ];

        return [
            'header' => $header,
            'rows' => $rows,
            'totals' => []
        ];
    }
}
