<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceExecutionController;
use App\Http\Controllers\Compliance\ProjectSettingsController;
use App\Http\Controllers\Compliance\SignatureController;
use App\Http\Controllers\ManualDataController;
use App\Http\Controllers\ManualUploadController;
use App\Http\Controllers\Compliance\CompliancePreviewController;
use App\Http\Controllers\Compliance\ComplianceTestAnalysisController;
use App\Http\Controllers\Compliance\ComplianceDiagnosticController;
use App\Http\Controllers\ManualComplianceController;
use App\Http\Controllers\ManualComplianceExecutionController;
use App\Http\Controllers\ComplianceDashboardController;


Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [ComplianceExecutionController::class, 'dashboard'])->name('compliance.dashboard');
    Route::get('/dashboard/testanalysisreport', [ComplianceTestAnalysisController::class, 'testAnalysisReport'])->name('compliance.testanalysis');

    Route::get('/forms/{section}', [ComplianceExecutionController::class, 'forms'])->name('compliance.forms');
    Route::post('/batch/create', [ComplianceExecutionController::class, 'createBatch'])->name('compliance.batch.create')->middleware('subscription.full');
    Route::post('/batch/create-minimal', [ComplianceExecutionController::class, 'createBatchMinimal'])->name('compliance.batch.create.minimal');
    Route::post('/manual-batch/create', [ManualComplianceController::class, 'createBatch'])->name('compliance.manual-batch.create');
    // Manual Compliance Dashboard
    Route::get('/manual-dashboard', [ComplianceDashboardController::class, 'dashboard'])->name('compliance.manual-dashboard');
    Route::get('/manual-batches', [ComplianceDashboardController::class, 'getTenantBatches'])->name('compliance.manual-batches');
    Route::get('/manual-batch/{batch_id}/summary', [ComplianceDashboardController::class, 'getBatchSummary'])->name('compliance.manual-batch.summary');
    Route::get('/manual-batch/{batch_id}/progress', [ComplianceDashboardController::class, 'getBatchSummary'])->name('compliance.manual-batch.progress');
    Route::get('/batch/{batchId}/timeline-status', [ComplianceDashboardController::class, 'getTimelineStatus'])->name('compliance.batch.timeline-status');
    Route::get('/manual-batch/{batch_id}', [ComplianceDashboardController::class, 'getBatchItems'])->name('compliance.manual-batch.items');
    Route::post('/manual-item/upload', [ManualComplianceExecutionController::class, 'complete'])->name('compliance.manual-item.upload');
    Route::post('/manual-item/skip', [ManualComplianceExecutionController::class, 'skip'])->name('compliance.manual-item.skip');
    Route::get('/manual-item/{itemId}/document', [ManualComplianceExecutionController::class, 'serveDocument'])->name('compliance.manual-item.document');
    Route::get('/batch/{batch}/review', [ComplianceExecutionController::class, 'reviewBatch'])->name('compliance.batch.review');

    Route::get('/batch/{batch}/download', [ComplianceExecutionController::class, 'download'])->name('compliance.batch.download');
    Route::post('/form/upload/{batch}/{form}', [ComplianceExecutionController::class, 'uploadForm'])->name('compliance.form.upload');
    Route::post('/batch/{batch}/process-uploads', [ManualUploadController::class, 'processManualUploads'])->name('compliance.batch.processUploads');

    Route::get('/settings', [ProjectSettingsController::class, 'index'])->name('compliance.settings');
    Route::post('/settings', [ProjectSettingsController::class, 'update'])->name('compliance.settings.update');

    // MINIMAL subscription routes - Manual data entry
    Route::get('/manual-data/{month}/{year}', [ManualDataController::class, 'show'])->name('compliance.manual-data.show');
    Route::post('/manual-data/{month}/{year}', [ManualDataController::class, 'save'])->name('compliance.manual-data.save');
    Route::post('/batch/{batch}/upload-csv', [ComplianceExecutionController::class, 'uploadDataFile'])->name('compliance.batch.uploadCsv');

    // Batch processing - Real-time with SSE (FULL only)
    Route::post('/batch/{batch}/process', [ComplianceExecutionController::class, 'processBatch'])->name('compliance.batch.process')->middleware('subscription.full');
    Route::get('/batch/{batch}/process-realtime', [ComplianceExecutionController::class, 'processBatchRealtime'])->name('compliance.batch.process.realtime')->middleware('subscription.full');
    Route::get('/batch/{batch}/status', [ComplianceExecutionController::class, 'getBatchStatus'])->name('compliance.batch.status');

    // Available to all subscribers
    Route::get('/preview/{formCode}', [CompliancePreviewController::class, 'preview'])->name('compliance.preview');
    Route::get('/batch/{batch}/preview/{form}', [ComplianceExecutionController::class, 'previewForm'])->name('compliance.batch.preview');
    Route::get('/batch/{batch}/pdf/{form}', [ComplianceExecutionController::class, 'downloadFormPdf'])->name('compliance.batch.form.pdf');
    Route::get('/batch/{batch}/review', [ComplianceExecutionController::class, 'reviewBatch'])->name('compliance.batch.review');
    Route::post('/batch/{batch}/re-audit/{form}', [ComplianceExecutionController::class, 'reAudit'])->name('compliance.batch.reAudit');
    Route::post('/batch/{batch}/fix-violations/{form}', [ComplianceExecutionController::class, 'fixViolations'])->name('compliance.batch.fixViolations');
    Route::post('/batch/{batch}/submit-fix/{form}', [ComplianceExecutionController::class, 'submitFix'])->name('compliance.batch.submitFix');

    // FULL subscription routes
    Route::middleware(['subscription.full'])->group(function () {
        Route::get('/batch/{batch}/form/{form}/refresh', [ComplianceExecutionController::class, 'refreshFormData'])->name('compliance.form.refresh');
        Route::get('/batch/{batch}/inspection-pack', [ComplianceExecutionController::class, 'downloadInspectionPack'])->name('compliance.batch.inspectionPack');

        // Digital Signature Routes
        Route::post('/sign/{batch}/{form}', [SignatureController::class, 'sign'])->name('compliance.sign');
        Route::get('/verify/{batch}/{form}', [SignatureController::class, 'verify'])->name('compliance.verify');
        Route::get('/signature/{batch}/{form}', [SignatureController::class, 'getDetails'])->name('compliance.signature.details');
        Route::post('/batch/{batch}/lock', [SignatureController::class, 'lockBatch'])->name('compliance.batch.lock');
        Route::post('/batch/{batch}/unlock', [SignatureController::class, 'unlockBatch'])->name('compliance.batch.unlock');
    });
});
