<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\Audit\ComplianceAuditService;

class BackfillBatchAudits extends Command
{
    protected $signature = 'compliance:backfill-audits {--batch= : Audit a specific batch ID only}';
    protected $description = 'Run audit engine on all completed batches that have not been audited yet';

    public function handle(ComplianceAuditService $auditService): void
    {
        $query = ComplianceExecutionBatch::whereIn('status', ['completed', 'partial']);

        if ($this->option('batch')) {
            $query->where('id', $this->option('batch'));
        } else {
            // Only batches with no audit scores yet
            $auditedBatchIds = \DB::table('compliance_form_audit_scores')
                ->pluck('batch_id')
                ->unique()
                ->toArray();

            if (!empty($auditedBatchIds)) {
                $query->whereNotIn('id', $auditedBatchIds);
            }
        }

        $batches = $query->get();

        if ($batches->isEmpty()) {
            $this->info('All batches already audited.');
            return;
        }

        $this->info("Auditing {$batches->count()} batch(es)...");
        $bar = $this->output->createProgressBar($batches->count());
        $bar->start();

        foreach ($batches as $batch) {
            try {
                $result = $auditService->auditBatch($batch->id);
                $status = $result['batch_status'] ?? 'unknown';
                $score  = $result['batch_score'] ?? 0;
                $bar->advance();
                $this->newLine();
                $this->line("  Batch #{$batch->id} → score: {$score}, status: {$status}");
            } catch (\Throwable $e) {
                $bar->advance();
                $this->newLine();
                $this->warn("  Batch #{$batch->id} → failed: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done.');
    }
}
