<?php

namespace App\Jobs;

use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessComplianceBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $batchId
    ) {
        $this->onQueue('compliance');
    }

    public function handle(ComplianceExecutionService $service): void
    {
        try {
            Log::info("Job: Processing batch {$this->batchId}");
            
            $results = $service->processBatch($this->batchId);
            
            $batch = ComplianceExecutionBatch::findOrFail($this->batchId);
            $batch->update([
                'status' => $results['failed'] === 0 ? 'processed' : 'partial',
                'updated_at' => now()
            ]);
            
            Log::info("Job: Batch {$this->batchId} processed successfully", ['results' => $results]);
        } catch (\Exception $e) {
            Log::error("Job: Failed to process batch {$this->batchId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $batch = ComplianceExecutionBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }
}
