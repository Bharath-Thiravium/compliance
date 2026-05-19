<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleBatchProcessingService
{
    public function __construct(
        private ComplianceOrchestrator $orchestrator
    ) {}

    public function processBatch(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        $batchForms = ComplianceBatchForm::where('batch_id', $batchId)->get();
        
        $results = [];
        
        foreach ($batchForms as $batchForm) {
            try {
                // Execute orchestrator
                $result = $this->orchestrator->execute(
                    $batch->tenant_id,
                    $batch->branch_id,
                    $batch->period_month,
                    $batch->period_year,
                    $batchForm->form_code,
                    'batch',
                    $batchId
                );
                
                // Update status based on result
                if ($result['status'] === 'success') {
                    $filePath = $result['result']['file_path'] ?? null;
                    DB::table('compliance_batch_forms')
                        ->where('id', $batchForm->id)
                        ->update([
                            'status' => 'generated',
                            'file_path' => $filePath,
                            'updated_at' => now()
                        ]);
                    
                    $results[$batchForm->form_code] = [
                        'status' => 'generated',
                        'file_path' => $filePath
                    ];
                } else {
                    DB::table('compliance_batch_forms')
                        ->where('id', $batchForm->id)
                        ->update([
                            'status' => 'failed',
                            'updated_at' => now()
                        ]);
                    
                    $results[$batchForm->form_code] = [
                        'status' => 'failed',
                        'error' => $result['error'] ?? 'Unknown error'
                    ];
                }
            } catch (\Exception $e) {
                DB::table('compliance_batch_forms')
                    ->where('id', $batchForm->id)
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now()
                    ]);
                
                $results[$batchForm->form_code] = [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Update batch status
        $successful = count(array_filter($results, fn($r) => $r['status'] === 'generated'));
        $batch->update([
            'status' => $successful === count($results) ? 'processed' : 'partial',
            'updated_at' => now()
        ]);
        
        return $results;
    }
}
