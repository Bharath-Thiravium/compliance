<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXIVService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XIV');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('contract_labour_deployment as cl')
            ->join('contractor_master as c', 'c.id', '=', 'cl.contractor_id')
            ->leftJoin('workforce_employee as e', 'e.id', '=', 'cl.employee_id')
            ->where('cl.tenant_id', $tenantId)
            ->where('cl.branch_id', $branchId)
            ->whereBetween('cl.deployment_start', [$startDate, $endDate])
            ->select([
                'e.name',
                'e.employee_code',
                'e.designation',
                'cl.wage_rate as daily_rate',
                'cl.deployment_start as joining_date',
                'cl.deployment_end as tenure_end',
                'c.company_name as contractor_name',
                'c.company_address as contractor_address',
            ])
            ->orderBy('cl.deployment_start')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XIV', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branch = DB::table('branches')->where('id', $branchId)->first();
        
        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'NIL',
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
