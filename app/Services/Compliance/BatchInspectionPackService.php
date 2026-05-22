<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;
use Exception;

class BatchInspectionPackService
{
    private string $storagePath = 'compliance_inspection_packs';
    private array $formCategories = [
        'CLRA' => ['FORM_XII', 'FORM_XIII', 'FORM_XIV', 'FORM_XVI', 'FORM_XVII', 'FORM_XIX', 'FORM_XX', 'FORM_XXI', 'FORM_XXII', 'FORM_XXIII'],
        'Labour_Welfare' => ['FORM_A', 'FORM_C', 'FORM_D', 'FORM_DER'],
        'Social_Security' => ['FORM_11', 'ESI_FORM_12', 'EPF_INSPECTION'],
        'Factories_Act' => ['FORM_B', 'FORM_2', 'FORM_8', 'FORM_10', 'FORM_12', 'FORM_17', 'FORM_18', 'FORM_25', 'FORM_26', 'FORM_26A', 'HAZARD_REG'],
        'Shops_Establishment' => ['SHOPS_FORM_12', 'SHOPS_FORM_13', 'SHOPS_FORM_C', 'SHOPS_FORM_VI', 'SHOPS_UNPAID', 'SHOPS_FINES'],
    ];

    public function createInspectionPack(
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        ?array $formCodes = null
    ): string {
        try {
            // Verify data exists
            $this->verifyDataExists($tenantId, $branchId, $month, $year);

            // Create temporary directory
            $tempDir = storage_path('app/temp/inspection_pack_' . uniqid());
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate forms by category
            $formCodes = $formCodes ?? $this->getAllFormCodes();
            $generatedForms = $this->generateFormsByCategory($tempDir, $tenantId, $branchId, $month, $year, $formCodes);

            // Create manifest
            $this->createManifest($tempDir, $tenantId, $branchId, $month, $year, $generatedForms);

            // Create ZIP file
            $zipPath = $this->createZipFile($tempDir, $tenantId, $branchId, $month, $year);

            // Cleanup temp directory
            $this->cleanupDirectory($tempDir);

            return $zipPath;

        } catch (Exception $e) {
            throw new Exception("Failed to create inspection pack: " . $e->getMessage());
        }
    }

    private function verifyDataExists(int $tenantId, int $branchId, int $month, int $year): void
    {
        $count = DB::table('workforce_payroll_entry')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        if ($count === 0) {
            throw new Exception("No payroll data found for tenant {$tenantId}, branch {$branchId}, {$month}/{$year}");
        }
    }

    private function generateFormsByCategory(
        string $tempDir,
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        array $formCodes
    ): array {
        $generatedForms = [];
        $pdfService = app(PdfGenerationService::class);

        foreach ($this->formCategories as $category => $forms) {
            $categoryDir = $tempDir . '/' . str_replace('_', ' ', $category);
            if (!is_dir($categoryDir)) {
                mkdir($categoryDir, 0755, true);
            }

            foreach ($forms as $formCode) {
                if (!in_array($formCode, $formCodes)) {
                    continue;
                }

                try {
                    $pdfPath = $pdfService->generateFormPdf($formCode, $tenantId, $branchId, $month, $year);
                    
                    if (file_exists($pdfPath)) {
                        $fileName = $this->getFormFileName($formCode, $month, $year);
                        $destPath = $categoryDir . '/' . $fileName;
                        copy($pdfPath, $destPath);
                        
                        $generatedForms[] = [
                            'code' => $formCode,
                            'category' => $category,
                            'file' => $fileName,
                            'path' => $destPath,
                            'size' => filesize($destPath),
                        ];
                    }
                } catch (Exception $e) {
                    // Log error but continue with other forms
                    \Log::warning("Failed to generate {$formCode}: " . $e->getMessage());
                }
            }
        }

        return $generatedForms;
    }

    private function createManifest(
        string $tempDir,
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        array $generatedForms
    ): void {
        $tenant = DB::table('tenants')->find($tenantId);
        $branch = DB::table('branches')->find($branchId);

        $manifest = [
            'created_at' => now()->toIso8601String(),
            'tenant' => [
                'id' => $tenantId,
                'name' => $tenant->name ?? 'Unknown',
            ],
            'branch' => [
                'id' => $branchId,
                'name' => $branch->branch_name ?? 'Unknown',
            ],
            'period' => [
                'month' => $month,
                'year' => $year,
                'display' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            ],
            'forms' => [
                'total' => count($generatedForms),
                'by_category' => $this->groupFormsByCategory($generatedForms),
                'details' => $generatedForms,
            ],
            'statistics' => [
                'total_size' => array_sum(array_column($generatedForms, 'size')),
                'total_pages' => count($generatedForms) * 2, // Approximate
            ],
        ];

        file_put_contents(
            $tempDir . '/MANIFEST.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function groupFormsByCategory(array $forms): array
    {
        $grouped = [];
        foreach ($forms as $form) {
            if (!isset($grouped[$form['category']])) {
                $grouped[$form['category']] = [];
            }
            $grouped[$form['category']][] = $form['code'];
        }
        return $grouped;
    }

    private function createZipFile(
        string $tempDir,
        int $tenantId,
        int $branchId,
        int $month,
        int $year
    ): string {
        $zipFileName = sprintf(
            'inspection_pack_T%d_B%d_%04d_%02d_%s.zip',
            $tenantId,
            $branchId,
            $year,
            $month,
            now()->format('YmdHis')
        );

        $zipPath = storage_path('app/' . $this->storagePath . '/' . $zipFileName);

        // Ensure storage directory exists
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Failed to create ZIP file");
        }

        // Add all files recursively
        $this->addFilesToZip($zip, $tempDir, '');

        $zip->close();

        return $zipPath;
    }

    private function addFilesToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;
            $zipFilePath = $zipPath ? $zipPath . '/' . $file : $file;

            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipFilePath);
                $this->addFilesToZip($zip, $filePath, $zipFilePath);
            } else {
                $zip->addFile($filePath, $zipFilePath);
            }
        }
    }

    private function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->cleanupDirectory($filePath);
                rmdir($filePath);
            } else {
                unlink($filePath);
            }
        }

        rmdir($dir);
    }

    private function getFormFileName(string $formCode, int $month, int $year): string
    {
        $monthName = Carbon::createFromDate($year, $month, 1)->format('M');
        return str_replace('_', ' ', $formCode) . " {$monthName} {$year}.pdf";
    }

    private function getAllFormCodes(): array
    {
        $codes = [];
        foreach ($this->formCategories as $forms) {
            $codes = array_merge($codes, $forms);
        }
        return $codes;
    }

    public function getInspectionPackList(int $tenantId, int $branchId): array
    {
        $packDir = storage_path('app/' . $this->storagePath);
        if (!is_dir($packDir)) {
            return [];
        }

        $packs = [];
        $files = scandir($packDir, SCANDIR_SORT_DESCENDING);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.zip')) {
                continue;
            }

            if (preg_match("/T{$tenantId}_B{$branchId}/", $file)) {
                $filePath = $packDir . '/' . $file;
                $packs[] = [
                    'name' => $file,
                    'path' => $filePath,
                    'size' => filesize($filePath),
                    'created_at' => filemtime($filePath),
                    'url' => route('compliance.download-pack', ['file' => $file]),
                ];
            }
        }

        return $packs;
    }

    public function downloadPack(string $fileName): string
    {
        $filePath = storage_path('app/' . $this->storagePath . '/' . $fileName);

        if (!file_exists($filePath)) {
            throw new Exception("Inspection pack not found");
        }

        return $filePath;
    }
}
