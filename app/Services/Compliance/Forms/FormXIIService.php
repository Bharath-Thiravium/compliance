<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXIIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XII');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('contractor_master as c')
            ->leftJoin('contract_labour_deployment as cld', 'cld.contractor_id', '=', 'c.id')
            ->where('c.tenant_id', $tenantId)
            ->select([
                'c.company_name as contractor_name',
                'c.company_address as contractor_address',
                DB::raw("COALESCE(cld.nature_of_work, '') as nature_of_work"),
                DB::raw("COALESCE(cld.work_location, '') as work_location"),
                DB::raw("COALESCE(MIN(cld.deployment_start), '') as contract_from"),
                DB::raw("COALESCE(MAX(cld.deployment_end), '') as contract_to"),
                DB::raw('COUNT(DISTINCT cld.employee_id) as max_workers'),
            ])
            ->groupBy('c.id', 'c.company_name', 'c.company_address')
            ->orderBy('c.company_name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XII', $rows);

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

        if (empty($rows)) {
            return $this->nilResponse();
        }

        return [
            'header' => $header,
            'rows' => $rows,
            'totals' => []
        ];
    }
}
