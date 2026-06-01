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

        Log::info('FORM XIII: FETCH START', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period_start' => $this->periodStart->toDateString(),
            'period_end' => $this->periodEnd->toDateString(),
        ]);

        try {
            // STEP 1: Fetch all contractors for this tenant (branch_id is optional)
            $contractors = DB::table('contractor_master as cm')
                ->where('cm.tenant_id', $tenantId)
                ->whereNull('cm.deleted_at')
                ->select([
                    'cm.id',
                    DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                    DB::raw("COALESCE(cm.address, cm.company_address, 'N/A') as contractor_address"),
                ])
                ->get()
                ->keyBy('id')
                ->toArray();

            Log::info('FORM XIII: Contractors fetched from contractor_master', [
                'tenant_id' => $tenantId,
                'contractor_count' => count($contractors),
                'contractor_ids' => array_keys($contractors),
            ]);

            // STEP 2: Fetch all deployed employees for this tenant/branch
            // NOTE: branch_id is NULLABLE in contract_labour_deployment
            // So we need to handle both cases: branch_id = $branchId OR branch_id IS NULL
            $deployments = DB::table('contract_labour_deployment as cld')
                ->where('cld.tenant_id', $tenantId)
                ->where(function ($query) use ($branchId) {
                    $query->where('cld.branch_id', $branchId)
                          ->orWhereNull('cld.branch_id');
                })
                ->whereNull('cld.deleted_at')
                ->select([
                    'cld.id',
                    'cld.contractor_id',
                    'cld.employee_id',
                    'cld.deployment_start',
                    'cld.deployment_end',
                ])
                ->get()
                ->toArray();

            Log::info('FORM XIII: Deployments fetched from contract_labour_deployment', [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'deployment_count' => count($deployments),
                'deployment_ids' => array_column($deployments, 'id'),
            ]);

            if (empty($deployments)) {
                Log::warning('FORM XIII: No deployments found', [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                ]);
                return $this->buildResponse([], $tenantId, $branchId);
            }

            // STEP 3: Fetch all employees referenced in deployments
            $employeeIds = array_unique(array_column($deployments, 'employee_id'));
            $employees = DB::table('workforce_employee as we')
                ->whereIn('we.id', $employeeIds)
                ->whereNull('we.deleted_at')
                ->select([
                    'we.id',
                    'we.name',
                    'we.date_of_birth',
                    'we.gender',
                    'we.father_name',
                    'we.designation',
                    'we.permanent_address',
                    'we.local_address',
                ])
                ->get()
                ->keyBy('id')
                ->toArray();

            Log::info('FORM XIII: Employees fetched from workforce_employee', [
                'tenant_id' => $tenantId,
                'employee_count' => count($employees),
                'employee_ids' => array_keys($employees),
            ]);

            // STEP 4: Enrich deployments with employee and contractor details
            $enrichedRows = [];
            foreach ($deployments as $deployment) {
                $contractorId = $deployment->contractor_id;
                $employeeId = $deployment->employee_id;

                $contractor = $contractors[$contractorId] ?? null;
                $employee = $employees[$employeeId] ?? null;

                if (!$contractor) {
                    Log::warning('FORM XIII: Contractor not found', [
                        'contractor_id' => $contractorId,
                        'employee_id' => $employeeId,
                    ]);
                    continue;
                }

                if (!$employee) {
                    Log::warning('FORM XIII: Employee not found', [
                        'employee_id' => $employeeId,
                        'contractor_id' => $contractorId,
                    ]);
                    continue;
                }

                $enrichedRows[] = [
                    'contractor_id' => $contractorId,
                    'contractor_name' => $contractor->contractor_name,
                    'contractor_address' => $contractor->contractor_address,
                    'name' => $employee->name,
                    'date_of_birth' => $employee->date_of_birth,
                    'gender' => $employee->gender,
                    'father_name' => $employee->father_name,
                    'designation' => $employee->designation,
                    'permanent_address' => $employee->permanent_address,
                    'local_address' => $employee->local_address,
                    'joining_date' => $deployment->deployment_start,
                    'termination_date' => $deployment->deployment_end,
                ];

                Log::debug('FORM XIII: Row enriched', [
                    'contractor_id' => $contractorId,
                    'contractor_name' => $contractor->contractor_name,
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->name,
                ]);
            }

            Log::info('FORM XIII: Enrichment complete', [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'total_enriched_rows' => count($enrichedRows),
            ]);

        } catch (\Exception $e) {
            Log::error('FORM XIII FETCH ERROR', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'trace' => $e->getTraceAsString(),
            ]);
            $enrichedRows = [];
        }

        return $this->buildResponse($enrichedRows, $tenantId, $branchId);
    }

    private function buildResponse(array $records, int $tenantId, int $branchId): array
    {
        Log::info('FORM XIII FETCH COMPLETE', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'final_record_count' => count($records),
        ]);

        return [
            'records' => $records,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $this->month,
                'year' => $this->year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
