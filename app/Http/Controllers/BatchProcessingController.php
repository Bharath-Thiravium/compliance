<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchProcessingController extends Controller
{
    public function __construct(
        private ComplianceOrchestrator $orchestrator
    ) {}

    public function processNextForm(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            // Find next pending form
            $nextForm = ComplianceBatchForm::where('batch_id', $batch)
                ->where('status', 'pending')
                ->first();

            if (!$nextForm) {
                // All done — fetch final form state for UI sync
                $auditResult = app(\App\Services\Compliance\Audit\ComplianceAuditService::class)->auditBatch($batch);

                $forms     = ComplianceBatchForm::where('batch_id', $batch)->get();
                $generated = $forms->where('status', 'generated')->count();
                $failed    = $forms->where('status', 'failed')->count();
                $total     = $forms->count();

                $batchModel->update([
                    'status'       => $failed === 0 ? 'completed' : 'partial',
                    'processed_at' => now(),
                ]);

                // Run audit silently in background — does NOT affect user flow
                try {
                    app(\App\Services\Compliance\Audit\ComplianceAuditService::class)->auditBatch($batch);
                } catch (\Throwable) {}

                return response()->json([
                    'status'       => 'complete',
                    'batch_id'     => $batch,
                    'generated'    => $generated,
                    'failed'       => $failed,
                    'total'        => $total,
                    'progress'     => $total > 0 ? 100 : 0,
                    'forms'        => $forms->map(fn($f) => [
                        'form_code' => $f->form_code,
                        'status'    => $f->status,
                        'file_path' => $f->file_path,
                    ])->toArray(),
                    'audit'        => $auditResult,
                    'batch_score'  => $auditResult['batch_score']  ?? null,
                    'audit_status' => $auditResult['batch_status'] ?? null,
                ]);
            }

            // Mark as processing
            $nextForm->update(['status' => 'processing']);

            try {
                $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                    $batchModel->tenant_id,
                    $batchModel->branch_id
                );

                // Process the form
                $result = $this->orchestrator->execute(
                    $batchModel->tenant_id,
                    $branchId,
                    $batchModel->period_month,
                    $batchModel->period_year,
                    $nextForm->form_code,
                    'batch',
                    $batch
                );

                if ($result['status'] === 'success') {
                    $filePath = $result['result']['file_path'] ?? null;
                    $nextForm->update([
                        'status' => 'generated',
                        'file_path' => $filePath
                    ]);
                } else {
                    $nextForm->update(['status' => 'failed']);
                }
            } catch (\Throwable $e) {
                $nextForm->update(['status' => 'failed']);
                Log::error("Form processing error: {$nextForm->form_code}", ['error' => $e->getMessage()]);
            }

            // Get current progress
            $forms = ComplianceBatchForm::where('batch_id', $batch)->get();
            $generated = $forms->where('status', 'generated')->count();
            $total = $forms->count();

            return response()->json([
                'status' => 'processing',
                'batch_id' => $batch,
                'current_form' => $nextForm->form_code,
                'generated' => $generated,
                'total' => $total,
                'progress' => round(($generated / $total) * 100),
                'forms' => $forms->map(fn($f) => [
                    'form_code' => $f->form_code,
                    'status' => $f->status,
                    'file_path' => $f->file_path
                ])->toArray()
            ]);
        } catch (\Throwable $e) {
            Log::error('Batch processing error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBatchProgress(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            $forms = ComplianceBatchForm::where('batch_id', $batch)->get();
            $generated = $forms->where('status', 'generated')->count();
            $processing = $forms->where('status', 'processing')->count();
            $failed = $forms->where('status', 'failed')->count();
            $pending = $forms->where('status', 'pending')->count();
            $total = $forms->count();

            return response()->json([
                'batch_id' => $batch,
                'batch_status' => $batchModel->status,
                'generated' => $generated,
                'processing' => $processing,
                'failed' => $failed,
                'pending' => $pending,
                'total' => $total,
                'progress' => round(($generated / $total) * 100),
                'is_complete' => $pending === 0 && $processing === 0,
                'forms' => $forms->map(fn($f) => [
                    'form_code' => $f->form_code,
                    'status' => $f->status,
                    'file_path' => $f->file_path
                ])->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
