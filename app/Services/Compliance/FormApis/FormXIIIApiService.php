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

        try {
            // STEP 1: Fetch all contractors for this tenant/branch (like FORM XII)
            $contractors = DB::table('contractor_master as cm')
                ->where('cm.tenant_id', $tenantId)
                ->where('cm.branch_id', $branchId)
                ->whereNull('cm.deleted_at')
                ->select([
                    'cm.id',
                    DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                    DB::raw("COALESCE(cm.address, cm.company_address, 'N/A') as contractor_address"),
                ])
                ->get()
                ->keyBy('id')
                ->toArray();

            Log::info('FORM XIII: Contractors fetched', [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'contractor_count' => count($contractors),
            ]);

            // STEP 2: Fetch all deployed employees for these contractors
            $rows = DB::table('contract_labour_deployment as cld')
                ->join('workforce_employee as we', 'we.id', '=', 'cld.employee_id')
                ->where('cld.tenant_id', $tenantId)
                ->where('cld.branch_id', $branchId)
                ->whereNull('cld.deleted_at')
                ->whereNull('we.deleted_at')
                ->select([
                    'cld.contractor_id',
                    'we.name',
                    'we.date_of_birth',
                    'we.gender',
                    'we.father_name',
                    'we.designation',
                    'we.permanent_address',
                    'we.local_address',
                    'cld.deployment_start as joining_date',
                    'cld.deployment_end as termination_date',
                ])
                ->orderBy('cld.contractor_id')
                ->orderBy('cld.deployment_start')
                ->get()
                ->map(fn($row) => (array)$row)
                ->toArray();

            Log::info('FORM XIII: Employees fetched', [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_count' => count($rows),
            ]);

            // STEP 3: Enrich employee records with contractor details from contractor_master
            $enrichedRows = [];
            foreach ($rows as $row) {
                $contractorId = $row['contractor_id'];
                $contractor = $contractors[$contractorId] ?? null;
                
                if ($contractor) {
                    $row['contractor_name'] = $contractor->contractor_name;
                    $row['contractor_address'] = $contractor->contractor_address;
                    $enrichedRows[] = $row;
                    
                    Log::debug('FORM XIII: Row enriched', [
                        'contractor_id' => $contractorId,
                        'contractor_name' => $contractor->contractor_name,
                        'employee_name' => $row['name'],
                    ]);
                } else {
                    Log::warning('FORM XIII: Contractor not found for deployment', [
                        'contractor_id' => $contractorId,
                        'employee_name' => $row['name'],
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('FORM XIII FETCH ERROR', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'trace' => $e->getTraceAsString(),
            ]);
            $enrichedRows = [];
        }

        Log::info('FORM XIII FETCH COMPLETE', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'final_record_count' => count($enrichedRows),
        ]);

        return [
            'records' => $enrichedRows,
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
