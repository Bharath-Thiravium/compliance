<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Compliance\Debug\FormDebugger;

class EsiForm11Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('ESI_FORM_11');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month    = $month;
        $this->year     = $year;

        $rows = DB::table('incidents as i')
            ->join('workforce_employee as e', 'e.id', '=', 'i.employee_id')
            ->where('i.tenant_id', $tenantId)
            ->where('i.branch_id', $branchId)
            ->whereYear('i.notice_date', $year)
            ->whereMonth('i.notice_date', $month)
            ->select([
                'i.notice_date',
                'i.notice_time',
                'i.incident_date',
                'i.incident_time',
                'i.location',
                'i.cause',
                'i.injury_type',
                'i.activity',
                'i.first_aid_by',
                'i.witness',
                'i.remarks',
                'e.name',
                'e.permanent_address as address',
                'e.gender',
                DB::raw('YEAR(CURDATE()) - YEAR(e.date_of_birth) as age'),
                'e.esi_number',
                'e.designation',
            ])
            ->orderBy('i.notice_date')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        FormDebugger::end('ESI_FORM_11', $rows);

        Log::info('ESI_FORM_11 service', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period'    => "{$month}/{$year}",
            'rows'      => count($rows),
        ]);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branch = DB::table('branches')->where('id', $branchId)->where('tenant_id', $tenantId)->first();

        $header = [
            'company_name'       => $tenant?->name ?? 'N/A',
            'contractor_name'    => '',
            'total_workers'      => count($rows),
            'work_location'      => $branch?->address ?? 'N/A',
            'principal_employer' => $tenant?->name ?? 'N/A',
            'month_year'         => \Carbon\Carbon::create($year, $month, 1)->format('F Y'),
            'tenant'             => ['name' => $tenant?->name ?? 'N/A'],
            'branch'             => ['address' => $branch?->address ?? 'N/A'],
        ];

        return [
            'header'  => $header,
            'rows'    => $rows,
            'totals'  => [],
            'is_nil'  => empty($rows),
        ];
    }
}
