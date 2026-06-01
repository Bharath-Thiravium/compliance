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

        $timestamp = date('Y-m-d H:i:s');
        $uniqueId = uniqid('FORM_XIII_');
        
        Log::info("[$uniqueId] [$timestamp] FORM XIII FETCH STARTED", [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
        ]);

        $rows = [];

        try {
            // Get all deployments for this tenant
            $deployments = DB::table('contract_labour_deployment')
                ->where('tenant_id', $tenantId)
                ->get();

            Log::info("[$uniqueId] Total deployments found: " . count($deployments));

            foreach ($deployments as $deployment) {
                try {
                    $employee = DB::table('workforce_employee')
                        ->where('id', $deployment->employee_id)
                        ->first();

                    $contractor = DB::table('contractor_master')
                        ->where('id', $deployment->contractor_id)
                        ->first();

                    if ($employee && $contractor) {
                        $rows[] = [
                            'contractor_name' => $contractor->contractor_name ?? $contractor->company_name ?? 'N/A',
                            'contractor_address' => $contractor->address ?? $contractor->company_address ?? 'N/A',
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
                    }
                } catch (\Exception $e) {
                    Log::error("[$uniqueId] Error processing deployment", [
                        'deployment_id' => $deployment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("[$uniqueId] Successfully processed rows: " . count($rows));

        } catch (\Exception $e) {
            Log::error("[$uniqueId] Main query error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Log::info("[$uniqueId] [$timestamp] FORM XIII FETCH COMPLETED with " . count($rows) . " records");

        return [
            'records' => $rows,
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
