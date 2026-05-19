<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealtimeComplianceExecutionService
{
    public function __construct(
        private ComplianceOrchestrator $orchestrator
    ) {}

    public function processBatchRealtime(int $batchId, callable $progressCallback): array
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
        
        foreach ($batchForms as $index => $batchForm) {
            try {
                // Mark as processing
                DB::table('compliance_batch_forms')
                    ->where('id', $batchForm->id)
                    ->update(['status' => 'processing']);
                
                // Send progress
                $progressCallback([
                    'form_code' => $batchForm->form_code,
                    'status' => 'processing',
                    'progress' => round(($index / $results['total_forms']) * 100),
                    'current' => $index + 1,
                    'total' => $results['total_forms']
                ]);
                
                Log::info("Processing form {$batchForm->form_code} for batch {$batchId}");
                
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
                    $results['successful']++;
                    $results['forms'][$batchForm->form_code] = 'generated';
                    
                    // Send success progress
                    $progressCallback([
                        'form_code' => $batchForm->form_code,
                        'status' => 'generated',
                        'progress' => round((($index + 1) / $results['total_forms']) * 100),
                        'current' => $index + 1,
                        'total' => $results['total_forms']
                    ]);
                    
                    Log::info("Form {$batchForm->form_code} generated successfully");
                } else {
                    DB::table('compliance_batch_forms')
                        ->where('id', $batchForm->id)
                        ->update(['status' => 'failed']);
                    
                    $results['failed']++;
                    $results['forms'][$batchForm->form_code] = $result['error'] ?? 'Unknown error';
                    
                    // Send failed progress
                    $progressCallback([
                        'form_code' => $batchForm->form_code,
                        'status' => 'failed',
                        'error' => $result['error'] ?? 'Unknown error',
                        'progress' => round((($index + 1) / $results['total_forms']) * 100),
                        'current' => $index + 1,
                        'total' => $results['total_forms']
                    ]);
                    
                    Log::warning("Form {$batchForm->form_code} generation failed", [
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                DB::table('compliance_batch_forms')
                    ->where('id', $batchForm->id)
                    ->update(['status' => 'failed']);
                
                $results['failed']++;
                $results['forms'][$batchForm->form_code] = $e->getMessage();
                
                // Send error progress
                $progressCallback([
                    'form_code' => $batchForm->form_code,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'progress' => round((($index + 1) / $results['total_forms']) * 100),
                    'current' => $index + 1,
                    'total' => $results['total_forms']
                ]);
                
                Log::error("Exception processing form {$batchForm->form_code}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Update batch status
        $batch->update([
            'status' => $results['failed'] === 0 ? 'processed' : 'partial',
            'updated_at' => now()
        ]);
        
        Log::info("Batch {$batchId} processing complete", [
            'successful' => $results['successful'],
            'failed' => $results['failed'],
            'total' => $results['total_forms']
        ]);
        
        return $results;
    }
}
