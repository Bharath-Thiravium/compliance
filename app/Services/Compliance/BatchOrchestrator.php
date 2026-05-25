<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BatchOrchestrator
{
    public function __construct(
        private FrequencyEngine $frequencyEngine,
        private ManualComplianceLoaderService $manualLoader
    ) {}

    /**
     * Stage 1: Create batch and attach forms with pending status
     * NO form generation happens here
     */
    public function createBatch(
        int $tenantId,
        int $month,
        int $year
    ): ComplianceExecutionBatch {
        // Validate branch exists
        $branch = Branch::where('tenant_id', $tenantId)->first();
        if (!$branch) {
            throw new \Exception("No branch configured for this tenant.");
        }

        // Get default section
        $section = DB::table('compliance_sections')->first();
        if (!$section) {
            throw new \Exception("No statutory sections configured in the system.");
        }

        // Detect applicable forms by frequency
        $applicableForms = $this->frequencyEngine->getApplicableForms($month);
        if ($applicableForms->isEmpty()) {
            throw new \Exception("No forms applicable for month {$month}. Please ensure forms are configured in the system.");
        }

        // Calculate period dates
        $periodFrom = Carbon::create($year, $month, 1)->startOfMonth();
        $periodTo = Carbon::create($year, $month, 1)->endOfMonth();

        // Per-tenant sequential batch number (each tenant starts from 1)
        $userBatchNumber = ComplianceExecutionBatch::where('tenant_id', $tenantId)->count() + 1;

        // Create batch with pending status
        $batch = ComplianceExecutionBatch::create([
            'tenant_id'         => $tenantId,
            'branch_id'         => $branch->id,
            'section_id'        => $section->id,
            'period_month'      => $month,
            'period_year'       => $year,
            'period_from'       => $periodFrom,
            'period_to'         => $periodTo,
            'form_ids'          => json_encode($applicableForms->pluck('id')->toArray()),
            'status'            => 'pending',
            'created_by'        => auth()->id(),
            'user_batch_number' => $userBatchNumber,
        ]);

        // Attach forms to batch with pending status
        $this->attachFormsToBatch($batch, $applicableForms, $section->section_name);

        // Load manual compliances for batch
        $this->manualLoader->loadForBatch($batch);

        return $batch;
    }

    /**
     * Attach forms to batch with pending status
     * file_path is NULL until forms are generated in Stage 3
     */
    private function attachFormsToBatch(
        ComplianceExecutionBatch $batch,
        $applicableForms,
        string $sectionName
    ): void {
        $batchForms = [];

        foreach ($applicableForms as $form) {
            $batchForms[] = [
                'tenant_id' => $batch->tenant_id,
                'batch_id' => $batch->id,
                'form_code' => $form->form_code,
                'section' => $sectionName,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($batchForms)) {
            DB::table('compliance_batch_forms')->insert($batchForms);
        }
    }
}
