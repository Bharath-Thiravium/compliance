<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXIVApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee as we')
            ->leftJoin('contract_labour_deployment as cld', function ($join) {
                $join->on('cld.employee_id', '=', 'we.id')
                     ->whereRaw('cld.id = (SELECT MAX(id) FROM contract_labour_deployment WHERE employee_id = we.id)');
            })
            ->leftJoin('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            // Latest payroll entry for wage data
            ->leftJoin('workforce_payroll_entry as pe', function ($join) {
                $join->on('pe.employee_id', '=', 'we.id')
                     ->whereRaw('pe.id = (SELECT MAX(id) FROM workforce_payroll_entry WHERE employee_id = we.id)');
            })
            ->where('we.tenant_id', $tenantId)
            ->where('we.branch_id', $branchId)
            ->where('we.status', '=', 'active')
            ->select([
                'we.id',
                'we.employee_code',
                DB::raw("COALESCE(we.name, '')            as employee_name"),
                DB::raw("COALESCE(we.father_name, '')     as father_name"),
                DB::raw("COALESCE(we.designation, '')     as designation"),
                DB::raw("COALESCE(we.date_of_joining, cld.deployment_start, '') as date_of_joining"),
                // Use gross_salary from payroll; fall back to basic_salary on employee record
                DB::raw("COALESCE(NULLIF(pe.gross_salary, 0), NULLIF(we.basic_salary, 0), '') as wage_rate"),
                DB::raw("COALESCE(cm.contractor_name, cm.company_name, '') as contractor_name"),
                DB::raw("COALESCE(cld.work_description, '') as work_description"),
            ])
            ->orderBy('we.employee_code')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        return [
            'records'      => $rows,
            'meta'         => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant'       => $this->getTenantDetails($tenantId),
            'branch'       => $this->getBranchDetails($branchId, $tenantId),
            'period'       => $this->formatPeriod(),
            'record_count' => count($rows),
        ];
    }
}
