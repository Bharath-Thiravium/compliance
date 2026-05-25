<?php

namespace App\Http\Controllers;

use App\Models\ComplianceSection;
use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceExecutionService;
use App\Services\Compliance\ComplianceReportBuilder;
use App\Services\Compliance\ComplianceEngine;
use App\Services\Compliance\InspectionPackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\AuditLog;

class ComplianceExecutionController extends Controller
{
    private function auditLog(string $action, array $meta = []): void
    {
        try {
            AuditLog::create([
                'tenant_id'  => Auth::user()?->tenant_id ?? 0,
                'user_id'    => Auth::id(),
                'action'     => $action,
                'form_code'  => $meta['form_code'] ?? null,
                'batch_id'   => $meta['batch_id'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata'   => array_merge(['status' => 'success'], $meta),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {}
    }

    public function __construct(
        private ComplianceExecutionService $executionService,
        private ComplianceReportBuilder $reportBuilder,
        private ComplianceEngine $engine,
        private \App\Services\Compliance\ComplianceTimelineService $timelineService,
        private \App\Services\Compliance\Audit\ComplianceAuditService $auditService,
        private \App\Services\Compliance\Audit\ComplianceCorrectionService $correctionService
    ) {}

    private function subscription(): string
    {
        $user = Auth::user();
        // Super admins have no tenant — treat as FULL
        if ($user && $user->is_super_admin) {
            return 'FULL';
        }
        return $user->tenant->subscription_type ?? 'MINIMAL';
    }

    private function requireFullSubscription(): ?\Illuminate\Http\JsonResponse
    {
        if ($this->subscription() !== 'FULL') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Upgrade to FULL subscription to access automation.',
            ], 403);
        }
        return null;
    }

    public function dashboard()
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return redirect()->route('login');
            }

            // Tenant may be null if user was created without one
            if (! $user->tenant_id || ! $user->tenant) {
                return view('compliance.dashboard', $this->dashboardDefaults(
                    Auth::user(), 'Your account is not linked to a tenant. Contact your administrator.'
                ));
            }

            $subscription = $this->subscription();
            $tenantId     = $user->tenant_id;
            $branch       = \App\Models\Branch::where('tenant_id', $tenantId)->first();
            $sections     = ComplianceSection::where('is_active', true)->get();

            $batches = ComplianceExecutionBatch::with('section')
                ->where('tenant_id', $tenantId)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereNull('created_by');
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($batches as $batch) {
                $logs = DB::table('compliance_generation_logs')
                    ->where('batch_id', $batch->id)
                    ->pluck('status');

                $batch->display_status = match (true) {
                    $logs->isEmpty()                                                          => 'Pending',
                    $logs->contains('processing')                                             => 'Processing',
                    $logs->every(fn($s) => in_array($s, ['success', 'completed']))            => 'Completed',
                    $logs->every(fn($s) => $s === 'failed')                                   => 'Failed',
                    default                                                                   => 'Partially Completed',
                };

                $auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batch->id)->get();
                if ($auditLogs->isNotEmpty()) {
                    $passedCount        = $auditLogs->where('status', 'passed')->count();
                    $batch->audit_score  = round($auditLogs->avg('audit_score'));
                    $batch->audit_status = $passedCount === $auditLogs->count() ? 'Passed'
                        : ($passedCount === 0 ? 'Failed' : 'Partial');
                    $batch->audit_logs   = $auditLogs;
                } else {
                    $batch->audit_score  = null;
                    $batch->audit_status = 'Not Audited';
                    $batch->audit_logs   = collect();
                }
            }

            $healthScore      = app(\App\Services\Compliance\ComplianceHealthService::class)
                ->calculateScore($tenantId, now()->month, now()->year);
            $timelineMetrics  = $this->timelineService
                ->getTimelineMetrics($tenantId, now()->month, now()->year);
            $statutorySections = config('statutory_form_grouping.sections');
            $formCodeToId      = ComplianceFormsMaster::pluck('id', 'form_code')->toArray();

            return view('compliance.dashboard', compact(
                'sections', 'batches', 'subscription', 'branch', 'user',
                'healthScore', 'timelineMetrics', 'statutorySections', 'formCodeToId'
            ));

        } catch (\Throwable $e) {
            logger()->error('Dashboard Error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
            ]);
            return view('compliance.dashboard', $this->dashboardDefaults(
                Auth::user(), 'Failed to load dashboard: ' . $e->getMessage()
            ));
        }
    }

    private function dashboardDefaults(?\App\Models\User $user, string $error = ''): array
    {
        return [
            'sections'          => collect(),
            'batches'           => collect(),
            'subscription'      => 'MINIMAL',
            'branch'            => null,
            'user'              => $user,
            'healthScore'       => null,
            'timelineMetrics'   => null,
            'statutorySections' => config('statutory_form_grouping.sections', []),
            'formCodeToId'      => [],
            'error'             => $error,
        ];
    }

    public function forms(string $section)
    {
        try {
            $sectionModel = ComplianceSection::where('section_code', $section)
                ->orWhere('id', $section)
                ->firstOrFail();

            $forms = ComplianceFormsMaster::where('section_id', $sectionModel->id)
                ->where('is_active', true)
                ->get();

            return response()->json($forms);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function createBatch(Request $request)
    {
        if ($guard = $this->requireFullSubscription()) return $guard;

        try {
            $tenantId = Auth::user()->tenant_id;

            if (!$tenantId) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Super Admin accounts are not linked to a tenant. Please use a tenant user account to create compliance batches.',
                ], 422);
            }

            $validated = $request->validate([
                'period_month' => 'required|integer|min:1|max:12',
                'period_year'  => 'required|integer|min:2020|max:2030',
            ]);

            $batchOrchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
            $batch = $batchOrchestrator->createBatch(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            $this->timelineService->createTimelineOnBatchCreation(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            $dataAvailabilityEngine = app(\App\Services\Compliance\DataAvailabilityEngine::class);
            $availability = $dataAvailabilityEngine->checkDataAvailability(
                $tenantId,
                $batch->branch_id,
                $validated['period_month'],
                $validated['period_year']
            );

            $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch->id)
                ->get()
                ->map(fn($f) => [
                    'form_code' => $f->form_code,
                    'section'   => $f->section ?? '-',
                    'status'    => $f->status ?? 'pending',
                ])
                ->toArray();

            $period = Carbon::create($validated['period_year'], $validated['period_month'], 1)->format('F Y');

            $this->auditLog('batch_create', ['batch_id' => $batch->id, 'period' => $period]);

            return response()->json([
                'status'            => 'success',
                'batch_id'          => $batch->id,
                'period'            => $period,
                'forms'             => $forms,
                'data_availability' => $availability,
                'can_proceed'       => $availability['all_data_exists'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error('Batch Creation Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function createBatchMinimal(Request $request)
    {
        try {
            $tenantId = Auth::user()->tenant_id;

            if (!$tenantId) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Super Admin accounts are not linked to a tenant. Please use a tenant user account.',
                ], 422);
            }

            $validated = $request->validate([
                'period_month' => 'required|integer|min:1|max:12',
                'period_year'  => 'required|integer|min:2020|max:2030',
            ]);

            $batchOrchestrator = app(\App\Services\Compliance\BatchOrchestrator::class);
            $batch = $batchOrchestrator->createBatch(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            $this->timelineService->createTimelineOnBatchCreation(
                $tenantId,
                $validated['period_month'],
                $validated['period_year']
            );

            $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch->id)
                ->get()
                ->map(fn($f) => [
                    'form_code' => $f->form_code,
                    'section'   => $f->section ?? '-',
                    'status'    => $f->status ?? 'pending',
                ])
                ->toArray();

            $period = Carbon::create($validated['period_year'], $validated['period_month'], 1)->format('F Y');

            // MINIMAL: always require data input — never read from DB
            $availability = [
                'all_data_exists' => false,
                'missing_data'    => ['employees', 'attendance', 'payroll'],
                'data_summary'    => [
                    'employees'         => 0,
                    'attendance_records'=> 0,
                    'payroll_entries'   => 0,
                ],
            ];

            return response()->json([
                'status'            => 'success',
                'batch_id'          => $batch->id,
                'period'            => $period,
                'forms'             => $forms,
                'data_availability' => $availability,
                'can_proceed'       => false,
                'minimal_mode'      => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Minimal Batch Creation Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function previewForm(int $batch, string $form)
    {
        $batchModel = ComplianceExecutionBatch::findOrFail($batch);
        $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
            $batchModel->tenant_id,
            $batchModel->branch_id
        );

        $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
        $result = $orchestrator->execute(
            $batchModel->tenant_id,
            $branchId,
            $batchModel->period_month,
            $batchModel->period_year,
            $form,
            'preview',
            $batch
        );

        if ($result['status'] === 'failed') {
            abort(400, $result['error']);
        }

        $this->auditLog('preview_form', ['batch_id' => $batch, 'form_code' => $form]);

        return response($result['result']['html'])
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function downloadFormPdf(int $batch, string $form)
    {
        $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', $batch)
            ->firstOrFail();

        $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
            $batchModel->tenant_id,
            $batchModel->branch_id
        );

        $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
        $result = $orchestrator->execute(
            $batchModel->tenant_id,
            $branchId,
            $batchModel->period_month,
            $batchModel->period_year,
            $form,
            'preview',
            $batch
        );

        if ($result['status'] === 'failed') {
            abort(400, $result['error']);
        }

        $html = $result['result']['html'];

        $landscapeForms = ['FORM_2', 'FORM_10', 'FORM_11', 'FORM_12', 'FORM_17', 'FORM_18', 'FORM_25', 'ESI_FORM_11', 'FORM_XIII', 'FORM_XVII', 'FormXIII', 'FormXVII'];
        $orientation = in_array($form, $landscapeForms) ? 'landscape' : 'portrait';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('A4', $orientation)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('dpi', 96)
            ->setOption('defaultFont', 'Arial')
            ->setOption('chroot', [public_path()]);

        return $pdf->download("{$form}.pdf");
    }

    public function refreshFormData(int $batch, string $form)
    {
        try {
            $batchModel = ComplianceExecutionBatch::findOrFail($batch);

            if (Auth::check() && $batchModel->tenant_id !== Auth::user()->tenant_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $batchModel->tenant_id,
                $batchModel->branch_id
            );

            $orchestrator = app(\App\Services\Compliance\ComplianceOrchestrator::class);
            $result = $orchestrator->execute(
                $batchModel->tenant_id,
                $branchId,
                $batchModel->period_month,
                $batchModel->period_year,
                $form,
                'preview',
                $batch
            );

            if ($result['status'] === 'failed') {
                return response()->json(['error' => $result['error']], 400);
            }

            return response()->json([
                'rows' => $result['result']['rows'] ?? [],
                'totals' => $result['result']['totals'] ?? [],
                'is_nil' => $result['result']['is_nil'] ?? false,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processBatch(int $batch)
    {
        if ($guard = $this->requireFullSubscription()) return $guard;

        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            if ($batchModel->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Batch cannot be processed. Current status: {$batchModel->status}"
                ], 422);
            }

            // Mark as processing immediately
            $batchModel->update(['status' => 'processing']);

            // Use realtime service instead of queue
            $realtimeService = app(\App\Services\Compliance\RealtimeComplianceExecutionService::class);
            $results = $realtimeService->processBatchRealtime($batch, function($progress) {
                // This callback is called for each form
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Batch processing completed',
                'batch_id' => $batchModel->id,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Batch process error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process batch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processBatchRealtime(int $batch)
    {
        if ($guard = $this->requireFullSubscription()) return $guard;

        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            if ($batchModel->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Batch cannot be processed. Current status: {$batchModel->status}"
                ], 422);
            }

            // Mark as processing
            $batchModel->update(['status' => 'processing']);

            // Set up SSE response
            return response()->stream(function() use ($batch) {
                $realtimeService = app(\App\Services\Compliance\RealtimeComplianceExecutionService::class);
                
                $realtimeService->processBatchRealtime($batch, function($progress) {
                    echo "data: " . json_encode($progress) . "\n\n";
                    ob_flush();
                    flush();
                });
                
                echo "data: " . json_encode(['status' => 'complete']) . "\n\n";
                ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no'
            ]);
        } catch (\Exception $e) {
            Log::error('Batch realtime process error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process batch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processingScreen(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch)->get();

            return view('compliance.batch-processing', [
                'batch' => $batchModel,
                'forms' => $forms
            ]);
        } catch (\Exception $e) {
            Log::error('Processing screen error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to load processing screen: ' . $e->getMessage());
        }
    }

    public function getBatchStatus(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch)
                ->get()
                ->map(fn($f) => [
                    'form_code' => $f->form_code,
                    'status' => $f->status ?? 'pending',
                    'file_path' => $f->file_path
                ])
                ->toArray();

            return response()->json($forms);
        } catch (\Exception $e) {
            Log::error('Batch status error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reviewBatch(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch)->get();
            $form_count = $forms->count();

            $dataAvailabilityEngine = app(\App\Services\Compliance\DataAvailabilityEngine::class);
            $availability = $dataAvailabilityEngine->checkDataAvailability(
                $batchModel->tenant_id,
                $batchModel->branch_id,
                $batchModel->period_month,
                $batchModel->period_year
            );

            $all_data_exists = $availability['all_data_exists'] ?? false;
            $can_proceed = $all_data_exists && $batchModel->status === 'pending';
            $missing_data = $availability['missing_data'] ?? [];
            $data_summary = $availability['data_summary'] ?? [];

            return view('compliance.batch-review', [
                'batch' => $batchModel,
                'forms' => $forms,
                'form_count' => $form_count,
                'all_data_exists' => $all_data_exists,
                'can_proceed' => $can_proceed,
                'missing_data' => $missing_data,
                'data_summary' => $data_summary
            ]);
        } catch (\Exception $e) {
            Log::error('Batch review error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to load batch review: ' . $e->getMessage());
        }
    }
    public function download(int $id)
    {
        return $this->downloadInspectionPack($id);
    }

    public function uploadForm(Request $request, int $batchId, int $formId)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'file' => 'required|file|mimes:pdf|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $batch = ComplianceExecutionBatch::find($batchId);
            if (!$batch) {
                return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);
            }

            $form = \App\Models\ComplianceFormsMaster::find($formId);
            if (!$form) {
                return response()->json(['status' => 'error', 'message' => 'Form not found'], 404);
            }

            if (!Storage::disk('local')->exists('compliance/manual_uploads')) {
                Storage::disk('local')->makeDirectory('compliance/manual_uploads');
            }

            $file = $request->file('file');
            $fileName = "batch_{$batchId}_{$form->form_code}_" . time() . ".pdf";
            $filePath = $file->storeAs('compliance/manual_uploads', $fileName, 'local');

            DB::table('compliance_manual_uploads')->insert([
                'user_id' => Auth::id(),
                'batch_id' => $batchId,
                'form_code' => $form->form_code,
                'file_path' => $filePath,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'file_path' => $filePath
            ]);
        } catch (\Throwable $e) {
            logger()->error('Upload Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Server error occurred'], 500);
        }
    }

    public function downloadInspectionPack(int $batch)
    {
        try {
            // Verify tenant ownership before delegating
            ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)
                ->where('id', $batch)
                ->firstOrFail();

            $result = app(InspectionPackService::class)->generateZipPack($batch);

            $fileName = "inspection_pack_batch_{$batch}.zip";

            $this->auditLog('download_inspection_pack', ['batch_id' => $batch]);

        return response()->download($result['path'], $fileName, ['Content-Type' => 'application/zip']);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            logger()->error('Inspection Pack Error', ['batch_id' => $batch, 'error' => $e->getMessage()]);
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Failed to generate inspection pack: ' . $e->getMessage());
        }
    }

    public function processManualUploads($batchId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
            }

            if ($user->tenant->subscription_type !== 'FULL') {
                return response()->json(['status' => 'error', 'message' => 'Only FULL subscription can process manual uploads'], 403);
            }

            $uploads = DB::table('compliance_manual_uploads')->where('batch_id', $batchId)->get();
            if ($uploads->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'No uploads found'], 400);
            }

            foreach ($uploads as $upload) {
                DB::table('compliance_generation_logs')->updateOrInsert(
                    ['batch_id' => $batchId, 'form_code' => $upload->form_code],
                    ['tenant_id' => $user->tenant_id, 'status' => 'completed', 'generated_file_path' => $upload->file_path, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            DB::table('compliance_execution_batches')->where('id', $batchId)->update(['status' => 'processed', 'updated_at' => now()]);

            return response()->json(['status' => 'success', 'message' => 'Uploads processed successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadDataFile(Request $request, int $batchId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
            }

            if ($user->tenant->subscription_type !== 'FULL') {
                return response()->json(['status' => 'error', 'message' => 'File uploads only for FULL subscriptions'], 403);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240',
                'dataset_type' => 'required|string|in:employees,payroll,attendance'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            $batch = ComplianceExecutionBatch::where('tenant_id', $user->tenant_id)->where('id', $batchId)->firstOrFail();
            $file = $request->file('file');
            $datasetType = $request->input('dataset_type');

            $handle = fopen($file->getRealPath(), "r");
            $headers = fgetcsv($handle, 1000, ",");
            $headers = array_map('strtolower', array_map('trim', $headers));
            $recordsInserted = 0;

            DB::beginTransaction();
            try {
                DB::table('compliance_manual_data')->where('batch_id', $batch->id)->where('dataset_type', $datasetType)->delete();

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($headers) == count($data)) {
                        $row = array_combine($headers, $data);
                        DB::table('compliance_manual_data')->insert([
                            'tenant_id' => $user->tenant_id,
                            'batch_id' => $batch->id,
                            'dataset_type' => $datasetType,
                            'data_payload' => json_encode($row),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $recordsInserted++;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                throw $e;
            }

            fclose($handle);

            return response()->json([
                'status' => 'success',
                'message' => "Successfully stored {$recordsInserted} records",
                'records_inserted' => $recordsInserted
            ]);
        } catch (\Exception $e) {
            logger()->error('CSV Upload Error', ['batch_id' => $batchId, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to process file: ' . $e->getMessage()], 500);
        }
    }

    public function reAudit(int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)->where('id', $batchId)->firstOrFail();
            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe($batch->tenant_id, $batch->branch_id);

            $result = $this->auditService->reAuditForm($formCode, $batch->tenant_id, $branchId, $batch->period_month, $batch->period_year, $batchId);

            if ($result['status'] === 'success') {
                $batchAverageScore = \App\Models\ComplianceAuditLog::where('batch_id', $batchId)->avg('audit_score');
                $confidenceLabel = $batchAverageScore >= 90 ? 'Inspection Ready' : ($batchAverageScore >= 70 ? 'Moderate Risk' : 'High Risk');

                return response()->json([
                    'status' => 'success',
                    'form_code' => $formCode,
                    'form_score' => $result['new_score'],
                    'batch_average_score' => round($batchAverageScore),
                    'batch_status' => $result['audit_status'],
                    'violations' => $result['violations'],
                    'confidence_label' => $confidenceLabel,
                ]);
            }

            return response()->json($result, 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function fixViolations(int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)->where('id', $batchId)->firstOrFail();
            $result = $this->correctionService->fixFormViolations($batchId, $formCode);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function submitFix(Request $request, int $batchId, string $formCode)
    {
        try {
            $batch = ComplianceExecutionBatch::where('tenant_id', Auth::user()->tenant_id)->where('id', $batchId)->firstOrFail();
            $validated = $request->validate(['corrections' => 'required|array']);
            $result = $this->correctionService->fixWithUserInput($batchId, $formCode, $validated['corrections']);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
