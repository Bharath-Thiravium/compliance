<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Models\ComplianceAuditLog;
use App\Models\ManualComplianceBatchItem;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class InspectionPackService
{
    private string $outputDisk = 'public';
    private string $outputDir  = 'inspection_packs';

    // -------------------------------------------------------------------------
    // Existing method — untouched
    // -------------------------------------------------------------------------
    public function generateInspectionPack(int $batchId): string
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);

        $forms = ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'success')
            ->get();

        $failedForms = ComplianceAuditLog::where('batch_id', $batchId)
            ->where('status', 'failed')
            ->pluck('form_code');

        $forms = $forms->reject(function ($form) use ($failedForms) {
            return $failedForms->contains($form->form_code);
        });

        if ($forms->isEmpty()) {
            throw new \Exception('No valid forms available for inspection pack.');
        }

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = storage_path("app/temp/inspection_{$batchId}.zip");
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Unable to create inspection ZIP.');
        }

        $addedCount = 0;
        foreach ($forms as $form) {
            if (Storage::disk('local')->exists($form->file_path)) {
                $absolutePath = Storage::disk('local')->path($form->file_path);
                $zip->addFile($absolutePath, "{$form->form_code}.pdf");
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            throw new \Exception('No valid files found for inspection pack.');
        }

        return $zipPath;
    }

    // -------------------------------------------------------------------------
    // New method — ZIP inspection pack (automated + manual)
    // -------------------------------------------------------------------------

    /**
     * Generate a ZIP inspection pack with automated/ and manual/ folders.
     *
     * @return array{path: string, url: string, automated_count: int, manual_count: int, total_count: int}
     * @throws \Exception
     */
    public function generateZipPack(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);

        $this->assertNoPendingManualItems($batchId, $batch->tenant_id, $batch->branch_id);

        $automatedPaths = $this->resolveAutomatedPaths($batchId);
        $manualPaths    = $this->resolveManualPaths($batchId, $batch->tenant_id, $batch->branch_id);

        if (empty($automatedPaths) && empty($manualPaths)) {
            throw new \Exception('No valid PDF files found for inspection pack.');
        }

        $outputDir = Storage::disk($this->outputDisk)->path($this->outputDir);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $fileName = "inspection_pack_{$batchId}_" . now()->format('YmdHis') . '.zip';
        $zipPath  = $outputDir . DIRECTORY_SEPARATOR . $fileName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Unable to create inspection ZIP.');
        }

        foreach ($automatedPaths as $path) {
            $zip->addFile($path, 'automated/' . basename($path));
        }

        foreach ($manualPaths as $path) {
            $zip->addFile($path, 'manual/' . basename($path));
        }

        $zip->close();

        $relativeStoragePath = $this->outputDir . '/' . $fileName;

        return [
            'path'            => $zipPath,
            'url'             => Storage::disk($this->outputDisk)->url($relativeStoragePath),
            'automated_count' => count($automatedPaths),
            'manual_count'    => count($manualPaths),
            'total_count'     => count($automatedPaths) + count($manualPaths),
        ];
    }

    // -------------------------------------------------------------------------
    // New method — merged PDF inspection pack (automated + manual)
    // -------------------------------------------------------------------------

    /**
     * Generate a single merged PDF inspection pack for a batch.
     *
     * @return array{path: string, url: string, automated_count: int, manual_count: int, total_count: int}
     * @throws \Exception
     */
    public function generateMergedPack(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);

        $this->assertNoPendingManualItems($batchId, $batch->tenant_id, $batch->branch_id);

        $automatedPaths = $this->resolveAutomatedPaths($batchId);
        $manualPaths    = $this->resolveManualPaths($batchId, $batch->tenant_id, $batch->branch_id);

        $allPaths = array_merge($automatedPaths, $manualPaths);

        if (empty($allPaths)) {
            throw new \Exception('No valid PDF files found for inspection pack.');
        }

        $outputPath = $this->mergePdfs($allPaths, $batchId);

        return [
            'path'             => $outputPath,
            'url'              => Storage::disk($this->outputDisk)->url("{$this->outputDir}/" . basename($outputPath)),
            'automated_count'  => count($automatedPaths),
            'manual_count'     => count($manualPaths),
            'total_count'      => count($allPaths),
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Throw if any manual item for this batch is still pending.
     */
    private function assertNoPendingManualItems(int $batchId, int $tenantId, int $branchId): void
    {
        $pendingCount = ManualComplianceBatchItem::where('batch_id', $batchId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount > 0) {
            throw new \Exception(
                "Cannot generate inspection pack: {$pendingCount} manual compliance item(s) are still pending."
            );
        }
    }

    /**
     * Resolve absolute paths for all successful automated PDFs in the batch.
     *
     * @return string[]
     */
    private function resolveAutomatedPaths(int $batchId): array
    {
        $failedForms = ComplianceAuditLog::where('batch_id', $batchId)
            ->where('status', 'failed')
            ->pluck('form_code');

        return ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'generated')
            ->whereNotNull('file_path')
            ->get()
            ->reject(fn ($form) => $failedForms->contains($form->form_code))
            ->map(fn ($form) => Storage::disk('local')->path($form->file_path))
            ->filter(fn ($path) => $this->isValidPdf($path))
            ->values()
            ->all();
    }

    /**
     * Resolve absolute paths for all uploaded manual PDFs in the batch.
     *
     * @return string[]
     */
    private function resolveManualPaths(int $batchId, int $tenantId, int $branchId): array
    {
        return ManualComplianceBatchItem::where('batch_id', $batchId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNotNull('document_path')
            ->where('document_path', '!=', '')
            ->get()
            ->map(fn ($item) => Storage::disk('public')->path($item->document_path))
            ->filter(fn ($path) => $this->isValidPdf($path))
            ->values()
            ->all();
    }

    /**
     * Merge an array of PDF file paths into a single PDF using FPDI.
     */
    private function mergePdfs(array $paths, int $batchId): string
    {
        $pdf = new Fpdi();

        foreach ($paths as $filePath) {
            try {
                $pageCount = $pdf->setSourceFile($filePath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl  = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);

                    $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            } catch (\Exception $e) {
                \Log::warning("InspectionPackService: skipping unreadable PDF [{$filePath}]: " . $e->getMessage());
            }
        }

        $outputDir = Storage::disk($this->outputDisk)->path($this->outputDir);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $fileName   = "inspection_pack_{$batchId}_" . now()->format('YmdHis') . '.pdf';
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $fileName;

        $pdf->Output('F', $outputPath);

        return $outputPath;
    }

    /**
     * Return true only if the path is a non-empty, readable file.
     */
    private function isValidPdf(string $path): bool
    {
        return !empty($path) && file_exists($path) && is_readable($path) && filesize($path) > 0;
    }
}
