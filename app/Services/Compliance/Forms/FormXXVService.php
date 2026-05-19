<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXXVService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XXV');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        $rows = DB::table('contractor_master as c')
            ->leftJoin('contract_labour_deployment as cld', 'cld.contractor_id', '=', 'c.id')
            ->where('c.tenant_id', $tenantId)
            ->select([
                'c.company_name',
                DB::raw("COALESCE(cld.nature_of_work, '') as nature_of_work"),
                DB::raw("COUNT(DISTINCT cld.employee_id) as total_workers"),
                DB::raw("0 as total_wages"),
                DB::raw("0 as total_deductions"),
            ])
            ->groupBy('c.id', 'c.company_name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_XXV', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'N/A',
            ],
            'period' => $month <= 6 ? "H1-$year" : "H2-$year",
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
