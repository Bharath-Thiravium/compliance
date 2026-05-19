<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplianceExecutionService
{
    public function __construct(
        private ComplianceOrchestrator $orchestrator
    ) {}

    public function processBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        
        $results = [
            'batch_id' => $batchId,
            'total_forms' => 0,
            'successful' => 0,
            'failed' => 0,
            'forms' => []
        ];
        
        $batchForms = ComplianceBatchForm::where('batch_id', $batchId)->get();
        $results['total_forms'] = $batchForms->count();
        
        foreach ($batchForms as $batchForm) {
            try {
                // Mark as processing
                ComplianceBatchForm::where('id', $batchForm->id)->update(['status' => 'processing']);
                Log::info("Service: Processing form {$batchForm->form_code} for batch {$batchId}");
                
                $result = $this->orchestrator->execute(
                    $batch->tenant_id,
                    $batch->branch_id,
                    $batch->period_month,
                    $batch->period_year,
                    $batchForm->form_code,
                    'batch',
                    $batchId
                );
                
                if ($result['status'] === 'success') {
                    // Orchestrator already updated file_path, just verify it
                    $form = ComplianceBatchForm::find($batchForm->id);
                    $results['successful']++;
                    $results['forms'][$batchForm->form_code] = 'generated';
                    
                    Log::info("Service: Form {$batchForm->form_code} generated successfully", [
                        'file_path' => $form->file_path
                    ]);
                } else {
                    // Mark as failed
                    ComplianceBatchForm::where('id', $batchForm->id)->update(['status' => 'failed']);
                    
                    $results['failed']++;
                    $results['forms'][$batchForm->form_code] = $result['error'] ?? 'Unknown error';
                    
                    Log::warning("Service: Form {$batchForm->form_code} generation failed", [
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                // Mark as failed
                ComplianceBatchForm::where('id', $batchForm->id)->update(['status' => 'failed']);
                
                $results['failed']++;
                $results['forms'][$batchForm->form_code] = $e->getMessage();
                
                Log::error("Service: Exception processing form {$batchForm->form_code}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        Log::info("Service: Batch {$batchId} processing complete", [
            'successful' => $results['successful'],
            'failed' => $results['failed'],
            'total' => $results['total_forms']
        ]);
        
        return $results;
    }
}
