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
        ]);

        try {
            // STEP 1: Fetch all contractors for this tenant
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

            Log::info('FORM XIII: Contractors fetched', [
                'contractor_count' => count($contractors),
            ]);

            // STEP 2: Fetch all contract labour records for this tenant
            // Use contract_labour table (not contract_labour_deployment)
            $rows = DB::table('contract_labour as cl')
                ->join('workforce_employee as we', 'we.id', '=', 'cl.employee_id')
                ->where('cl.tenant_id', $tenantId)
                ->whereNull('cl.deleted_at')
                ->whereNull('we.deleted_at')
                ->select([
                    'cl.contractor_id',
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
                ->toArray();

            Log::info('FORM XIII: Contract labour records fetched', [
                'record_count' => count($rows),
            ]);

            if (empty($rows)) {
                Log::warning('FORM XIII: No contract labour records found', [
                    'tenant_id' => $tenantId,
                ]);
                return $this->buildResponse([], $tenantId, $branchId);
            }

            // STEP 3: Enrich records with contractor details
            $enrichedRows = [];
            foreach ($rows as $row) {
                $contractorId = $row->contractor_id;
                $contractor = $contractors[$contractorId] ?? null;

                if (!$contractor) {
                    Log::warning('FORM XIII: Contractor not found', [
                        'contractor_id' => $contractorId,
                        'employee_name' => $row->name,
                    ]);
                    continue;
                }

                $enrichedRows[] = [
                    'contractor_id' => $contractorId,
                    'contractor_name' => $contractor->contractor_name,
                    'contractor_address' => $contractor->contractor_address,
                    'name' => $row->name,
                    'date_of_birth' => $row->date_of_birth,
                    'gender' => $row->gender,
                    'father_name' => $row->father_name,
                    'designation' => $row->designation,
                    'permanent_address' => $row->permanent_address,
                    'local_address' => $row->local_address,
                    'joining_date' => $row->joining_date,
                    'termination_date' => $row->termination_date,
                ];

                Log::debug('FORM XIII: Row enriched', [
                    'contractor_id' => $contractorId,
                    'contractor_name' => $contractor->contractor_name,
                    'employee_name' => $row->name,
                ]);
            }

            Log::info('FORM XIII: Enrichment complete', [
                'total_enriched_rows' => count($enrichedRows),
            ]);

        } catch (\Exception $e) {
            Log::error('FORM XIII FETCH ERROR', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
                'trace' => $e->getTraceAsString(),
            ]);
            $enrichedRows = [];
        }

        return $this->buildResponse($enrichedRows, $tenantId, $branchId);
    }

    private function buildResponse(array $records, int $tenantId, int $branchId): array
    {
        Log::info('FORM XIII FETCH COMPLETE', [
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
