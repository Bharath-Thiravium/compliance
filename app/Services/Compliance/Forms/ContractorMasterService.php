<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class ContractorMasterService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('CONTRACTOR_MASTER');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        $rows = DB::table('contractor_master as c')
            ->where('c.tenant_id', $tenantId)
            ->select([
                'c.company_name',
                DB::raw("COALESCE(c.company_address, '') as company_address"),
                DB::raw("COALESCE(c.contact_person, '') as contact_person"),
                DB::raw("COALESCE(c.contact_number, '') as contact_number"),
                DB::raw("COALESCE(c.email, '') as email"),
                DB::raw("COALESCE(c.license_number, '') as license_number"),
                DB::raw("COALESCE(c.license_date, '') as license_date"),
            ])
            ->orderBy('c.company_name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('CONTRACTOR_MASTER', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $header = [
            'tenant' => [
                'name' => $tenant?->name ?? 'N/A',
            ],
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
