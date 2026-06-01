<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Show all contract labour deployments — not period-filtered (contract labour is cumulative)
        try {
            $rows = DB::table('contract_labour as cl')
                ->join('workforce_employee as we', 'we.id', '=', 'cl.employee_id')
                ->where('cl.tenant_id', $tenantId)
                ->whereNull('cl.deleted_at')
                ->whereNull('we.deleted_at')
                ->select([
                    'we.name',
                    'we.date_of_birth',
                    'we.gender',
                    'we.father_name',
                    'we.designation',
                    'we.permanent_address',
                    'we.local_address',
                    'cl.employment_start as joining_date',
                    'cl.employment_end as termination_date',
                ])
                ->orderBy('cl.employment_start')
                ->get()
                ->map(fn($row) => (array)$row)
                ->toArray();
        } catch (\Exception $e) {
            Log::error('FORM XIII FETCH ERROR', ['error' => $e->getMessage(), 'tenant_id' => $tenantId]);
            $rows = [];
        }

        Log::info('FORM XIII FETCH', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'record_count' => count($rows),
        ]);

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
