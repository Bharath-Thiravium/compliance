<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;
use App\Models\Tenant;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\Registry\FormTemplateRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ZipArchive;

class ComplianceOrchestrator
{
    public function __construct(
        private StrictDataValidator $dataValidator,
        private PayrollValidationGuard $payrollValidator,
        private ProductionValidationGuard $productionValidator,
        private FormDataAggregator $aggregator,
        private FormGeneratorFactory $factory
    ) {}

    public function execute(
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        string $formCode,
        string $mode = 'batch',
        ?int $batchId = null
    ): array {
        $this->validateSubscriptionAccess($tenantId, $mode);
        $startTime = microtime(true);

        try {
            $this->validateInputs($tenantId, $branchId, $month, $year, $formCode);
            $this->runValidationPipeline($tenantId, $branchId, $month, $year);

            $apiService = FormApiServiceFactory::make($formCode);
            if ($apiService) {
                $rawData = $apiService->fetch($tenantId, $branchId, $month, $year);
            } else {
                $rawData = $this->aggregator->aggregate($formCode, $tenantId, $branchId, $month, $year);
            }

            if (isset($rawData['meta']['tenant_id']) && $rawData['meta']['tenant_id'] !== $tenantId) {
                throw new \Exception("Tenant ID mismatch in API response");
            }
            if (isset($rawData['meta']['branch_id']) && $rawData['meta']['branch_id'] !== $branchId) {
                throw new \Exception("Branch ID mismatch in API response");
            }

            $generator = $this->factory::make($formCode);
            if (!$generator) {
                throw new \Exception("No generator found for {$formCode}");
            }

            $formData = $generator->generate($rawData);

            $this->dataValidator->validateFormData($formCode, $formData);
            $this->payrollValidator->validateBeforeRender($formData['rows'] ?? []);

            $result = match ($mode) {
                'preview' => $this->executePreview($formCode, $formData, $month, $year, $batchId),
                'pdf' => $this->executePdf($formCode, $formData, $month, $year),
                'batch' => $this->executeBatch($formCode, $formData, $tenantId, $branchId, $batchId, $month, $year),
                'inspection_pack' => $this->executeInspectionPack($formCode, $formData, $tenantId, $branchId, $batchId, $month, $year),
                default => throw new \Exception("Invalid execution mode: {$mode}")
            };

            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->logExecution($tenantId, $branchId, $batchId ?? 0, $formCode, 'success', $executionTime, count($formData['rows'] ?? []), null, $mode);

            return [
                'status' => 'success',
                'mode' => $mode,
                'form_code' => $formCode,
                'execution_time' => $executionTime,
                'records_generated' => count($formData['rows'] ?? []),
                'result' => $result
            ];
        } catch (\Throwable $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->logExecution($tenantId, $branchId, $batchId ?? 0, $formCode, 'failed', $executionTime, 0, $e->getMessage(), $mode);

            return [
                'status' => 'failed',
                'mode' => $mode,
                'form_code' => $formCode,
                'execution_time' => $executionTime,
                'error' => $e->getMessage()
            ];
        }
    }

    public function executeBatch(string $formCode, array $formData, int $tenantId, int $branchId, ?int $batchId, int $month, int $year): array
    {
        $generator = $this->factory::make($formCode);
        
        $formData['form_code'] = $formCode;
        $formData['batch_id'] = $batchId ?? 0;
        $formData['period_month'] = $month;
        $formData['period_year'] = $year;

        // Render preview HTML once — PDF uses the same HTML (single render pipeline)
        $formData['rendered_html'] = $this->renderPreviewHtml($formCode, $formData, $month, $year, $batchId);
        
        \Log::info("Orchestrator: Generating PDF for {$formCode}", ['batch_id' => $batchId]);
        $pdfContent = $generator->generatePdf($formData);

        if (!$pdfContent || strlen($pdfContent) === 0) {
            throw new \Exception("PDF generation returned empty content for {$formCode}");
        }

        $directory = "generated_forms/{$tenantId}/{$batchId}";
        Storage::disk('local')->makeDirectory($directory);

        $fileName = "{$formCode}.pdf";
        $filePath = "{$directory}/{$fileName}";
        Storage::disk('local')->put($filePath, $pdfContent);

        if (!Storage::disk('local')->exists($filePath)) {
            throw new \Exception("Failed to store PDF for {$formCode}");
        }

        // Always update file_path when batch is provided
        if ($batchId) {
            \Log::info("Orchestrator: Updating batch form {$formCode} for batch {$batchId}", ['file_path' => $filePath]);
            
            $updated = DB::table('compliance_batch_forms')
                ->where('batch_id', $batchId)
                ->where('form_code', $formCode)
                ->update([
                    'file_path' => $filePath,
                    'status' => 'generated',
                    'updated_at' => now(),
                ]);

            \Log::info("Orchestrator: Update result for {$formCode}", ['rows_updated' => $updated]);
            
            if ($updated === 0) {
                throw new \Exception("Failed to update batch form record for {$formCode}");
            }
        }

        return [
            'file_path' => $filePath,
            'file_size' => strlen($pdfContent),
            'stored' => true
        ];
    }

    public function executePdf(string $formCode, array $formData, int $month, int $year): array
    {
        $generator = $this->factory::make($formCode);
        
        $formData['form_code'] = $formCode;
        $formData['batch_id'] = 0;
        $formData['period_month'] = $month;
        $formData['period_year'] = $year;

        // Render preview HTML once — PDF uses the same HTML (single render pipeline)
        $formData['rendered_html'] = $this->renderPreviewHtml($formCode, $formData, $month, $year, null);
        
        $pdfContent = $generator->generatePdf($formData);

        if (!$pdfContent || strlen($pdfContent) === 0) {
            throw new \Exception("PDF generation returned empty content for {$formCode}");
        }

        return [
            'content' => $pdfContent,
            'size' => strlen($pdfContent),
            'mime_type' => 'application/pdf'
        ];
    }

    public function executePreview(string $formCode, array $formData, int $month, int $year, ?int $batchId = null): array
    {
        $viewPath = FormTemplateRegistry::resolve($formCode);

        if (!View::exists($viewPath)) {
            throw new \Exception("View not found for {$formCode}");
        }

        $viewData = array_merge(
            $formData['header'] ?? [],
            [
                'form_title'   => $formData['header']['form_title'] ?? $formCode,
                'form_code'    => $formCode,
                'period_month' => $month,
                'period_year'  => $year,
                'batch_id'     => $batchId ?? 0,
                'header'       => $formData['header'] ?? [],
                'rows'         => $formData['rows'] ?? [],
                'relay_groups' => $formData['relay_groups'] ?? [],
                'data'         => $formData['data'] ?? [],
                'entries'      => $formData['rows'] ?? [],
                'cards'        => $formData['cards'] ?? [],
                'slips'        => $formData['slips'] ?? [],
                'totals'       => $formData['totals'] ?? [],
                'is_nil'       => $formData['is_nil'] ?? empty($formData['rows']),
                'nil_message'  => $formData['nil_message'] ?? '',
            ]
        );

        $html = View::make($viewPath, $viewData)->render();

        return [
            'html'       => $html,
            'is_nil'     => $formData['is_nil'] ?? false,
            'rows_count' => count($formData['rows'] ?? [])
        ];
    }

    public function executeInspectionPack(string $formCode, array $formData, int $tenantId, int $branchId, ?int $batchId, int $month = 0, int $year = 0): array
    {
        $generator = $this->factory::make($formCode);

        // Render preview HTML once — PDF uses the same HTML (single render pipeline)
        $formData['rendered_html'] = $this->renderPreviewHtml($formCode, $formData, $month, $year, $batchId);

        $pdfContent = $generator->generatePdf($formData);

        if (!$pdfContent || strlen($pdfContent) === 0) {
            throw new \Exception("PDF generation returned empty content for {$formCode}");
        }

        $packDir = "compliance_inspection_packs/{$tenantId}/{$batchId}";
        Storage::disk('local')->makeDirectory($packDir);

        $pdfFileName = "{$formCode}.pdf";
        $pdfPath = "{$packDir}/{$pdfFileName}";
        Storage::disk('local')->put($pdfPath, $pdfContent);

        $zipFileName = "inspection_pack_{$batchId}_" . time() . ".zip";
        $zipPath = storage_path("app/{$packDir}/{$zipFileName}");

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \Exception("Failed to create ZIP archive");
        }

        $zip->addFile(storage_path("app/{$pdfPath}"), $pdfFileName);
        $zip->close();

        return [
            'zip_path' => "{$packDir}/{$zipFileName}",
            'zip_size' => filesize($zipPath),
            'file_count' => 1,
            'created' => true
        ];
    }

    /**
     * Render the Blade view to HTML — the single source of truth for both preview and PDF.
     */
    private function renderPreviewHtml(string $formCode, array $formData, int $month, int $year, ?int $batchId): string
    {
        $viewPath = FormTemplateRegistry::resolve($formCode);

        $viewData = array_merge(
            $formData['header'] ?? [],
            [
                'form_title'   => $formData['header']['form_title'] ?? $formCode,
                'form_code'    => $formCode,
                'period_month' => $month,
                'period_year'  => $year,
                'batch_id'     => $batchId ?? 0,
                'header'       => $formData['header'] ?? [],
                'rows'         => $formData['rows'] ?? [],
                'relay_groups' => $formData['relay_groups'] ?? [],
                'data'         => $formData['data'] ?? [],
                'entries'      => $formData['rows'] ?? [],
                'cards'        => $formData['cards'] ?? [],
                'slips'        => $formData['slips'] ?? [],
                'totals'       => $formData['totals'] ?? [],
                'is_nil'       => $formData['is_nil'] ?? empty($formData['rows']),
                'nil_message'  => $formData['nil_message'] ?? '',
            ]
        );

        return View::make($viewPath, $viewData)->render();
    }

    private function runValidationPipeline(int $tenantId, int $branchId, int $month, int $year): void
    {
        $tenantValidation = $this->dataValidator->validateTenantSetup($tenantId);
        if (!$tenantValidation['valid']) {
            throw new \Exception("Tenant validation failed: " . implode(', ', $tenantValidation['errors']));
        }

        $branchValidation = $this->dataValidator->validateBranchSetup($branchId);
        if (!$branchValidation['valid']) {
            throw new \Exception("Branch validation failed: " . implode(', ', $branchValidation['errors']));
        }

        try {
            $this->productionValidator->validateBeforeGeneration($tenantId, $branchId, $month, $year);
        } catch (\Exception $e) {
            logger()->warning("Production validation warning: " . $e->getMessage());
        }
    }

    private function validateInputs(int $tenantId, int $branchId, int $month, int $year, string $formCode): void
    {
        if ($tenantId <= 0) {
            throw new \Exception("Invalid tenant_id: {$tenantId}");
        }

        if ($branchId <= 0) {
            throw new \Exception("Invalid branch_id: {$branchId}");
        }

        if ($month < 1 || $month > 12) {
            throw new \Exception("Invalid month: {$month}");
        }

        if ($year < 2020 || $year > 2030) {
            throw new \Exception("Invalid year: {$year}");
        }

        if (empty($formCode)) {
            throw new \Exception("Form code cannot be empty");
        }

        $form = ComplianceFormsMaster::where('form_code', $formCode)->first();
        if (!$form) {
            throw new \Exception("Form {$formCode} not found in master");
        }
    }

    private function validateSubscriptionAccess(int $tenantId, string $mode): void
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            throw new \Exception("Tenant {$tenantId} not found");
        }

        // Only inspection_pack requires FULL subscription
        if ($mode === 'inspection_pack' && $tenant->subscription_type !== 'FULL') {
            throw new \Exception("Subscription access denied. Inspection pack requires FULL subscription");
        }

        // Preview and PDF allowed for all subscriptions (for testing/development)
        // Batch mode allowed for all subscriptions
    }

    private function logExecution(
        int $tenantId,
        int $branchId,
        int $batchId,
        string $formCode,
        string $status,
        int $executionTime,
        int $recordsGenerated,
        ?string $errorMessage,
        string $mode
    ): void {
        // batch_id=0 means preview/pdf mode — no real batch row exists, skip to avoid FK violation
        if ($batchId === 0) return;

        try {
            DB::table('compliance_execution_logs')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'batch_id' => $batchId,
                'form_code' => $formCode,
                'status' => $status,
                'execution_time' => $executionTime,
                'records_generated' => $recordsGenerated,
                'error_message' => $errorMessage,
                'execution_mode' => $mode,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            logger()->error("Failed to log execution", ['error' => $e->getMessage()]);
        }
    }

    public function getExecutionLogs(int $batchId, ?string $formCode = null): array
    {
        $query = DB::table('compliance_execution_logs')
            ->where('batch_id', $batchId)
            ->orderBy('created_at', 'desc');
        if ($formCode) {
            $query->where('form_code', $formCode);
        }
        return json_decode(json_encode($query->get()->toArray()), true);
    }

    public function getExecutionStats(int $batchId): array
    {
        $logs = DB::table('compliance_execution_logs')
            ->where('batch_id', $batchId)
            ->get();
        $logsArray = collect(json_decode(json_encode($logs->toArray()), true));
        return [
            'total_executions'    => $logsArray->count(),
            'successful'          => $logsArray->where('status', 'success')->count(),
            'failed'              => $logsArray->where('status', 'failed')->count(),
            'total_execution_time'=> $logsArray->sum('execution_time'),
            'total_records'       => $logsArray->sum('records_generated'),
            'average_time'        => $logsArray->count() > 0 ? (int)($logsArray->sum('execution_time') / $logsArray->count()) : 0,
            'by_mode'             => $logsArray->groupBy('execution_mode')->map(fn($g) => [
                'count'      => $g->count(),
                'successful' => $g->where('status', 'success')->count(),
                'failed'     => $g->where('status', 'failed')->count(),
            ])->toArray(),
        ];
    }
}
