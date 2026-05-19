<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ComplianceFormsMaster;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\DB;

class VerifyCompliancePipeline extends Command
{
    protected $signature = 'compliance:verify-pipeline {--tenant_id=1} {--branch_id=1} {--month=1} {--year=2024}';
    protected $description = 'Verify complete pipeline for all 34 compliance forms';

    public function handle(ComplianceOrchestrator $orchestrator): int
    {
        $tenantId = (int)$this->option('tenant_id');
        $branchId = (int)$this->option('branch_id');
        $month = (int)$this->option('month');
        $year = (int)$this->option('year');

        $this->info("=== COMPLIANCE PIPELINE VERIFICATION ===\n");
        $this->line("Tenant: {$tenantId} | Branch: {$branchId} | Period: {$month}/{$year}\n");

        $forms = ComplianceFormsMaster::where('is_active', true)->get();
        $results = [];
        $stats = [
            'total' => 0,
            'preview_pass' => 0,
            'preview_fail' => 0,
            'pdf_pass' => 0,
            'pdf_fail' => 0,
            'batch_pass' => 0,
            'batch_fail' => 0,
        ];

        $this->output->progressStart($forms->count() * 3);

        foreach ($forms as $form) {
            $formCode = $form->form_code;
            $stats['total']++;

            // Test Preview
            try {
                $result = $orchestrator->execute($tenantId, $branchId, $month, $year, $formCode, 'preview');
                if ($result['status'] === 'success') {
                    $results[$formCode]['preview'] = 'PASS';
                    $stats['preview_pass']++;
                } else {
                    $results[$formCode]['preview'] = 'FAIL: ' . ($result['error'] ?? 'Unknown error');
                    $stats['preview_fail']++;
                }
            } catch (\Exception $e) {
                $results[$formCode]['preview'] = 'ERROR: ' . $e->getMessage();
                $stats['preview_fail']++;
            }
            $this->output->progressAdvance();

            // Test PDF
            try {
                $result = $orchestrator->execute($tenantId, $branchId, $month, $year, $formCode, 'pdf');
                if ($result['status'] === 'success' && isset($result['result']['content'])) {
                    $results[$formCode]['pdf'] = 'PASS';
                    $stats['pdf_pass']++;
                } else {
                    $results[$formCode]['pdf'] = 'FAIL: ' . ($result['error'] ?? 'No content');
                    $stats['pdf_fail']++;
                }
            } catch (\Exception $e) {
                $results[$formCode]['pdf'] = 'ERROR: ' . $e->getMessage();
                $stats['pdf_fail']++;
            }
            $this->output->progressAdvance();

            // Test Batch
            try {
                $batchId = 1;
                $result = $orchestrator->execute($tenantId, $branchId, $month, $year, $formCode, 'batch', $batchId);
                if ($result['status'] === 'success' && isset($result['result']['file_path'])) {
                    $results[$formCode]['batch'] = 'PASS';
                    $stats['batch_pass']++;
                } else {
                    $results[$formCode]['batch'] = 'FAIL: ' . ($result['error'] ?? 'No file path');
                    $stats['batch_fail']++;
                }
            } catch (\Exception $e) {
                $results[$formCode]['batch'] = 'ERROR: ' . $e->getMessage();
                $stats['batch_fail']++;
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine(2);

        // Display results table
        $this->table(
            ['Form Code', 'Preview', 'PDF', 'Batch'],
            array_map(fn($code, $result) => [
                $code,
                $result['preview'] ?? 'N/A',
                $result['pdf'] ?? 'N/A',
                $result['batch'] ?? 'N/A'
            ], array_keys($results), $results)
        );

        $this->newLine();
        $this->info("=== VERIFICATION SUMMARY ===");
        $this->line("Total Forms: {$stats['total']}");
        $this->line("Preview: {$stats['preview_pass']} PASS, {$stats['preview_fail']} FAIL");
        $this->line("PDF: {$stats['pdf_pass']} PASS, {$stats['pdf_fail']} FAIL");
        $this->line("Batch: {$stats['batch_pass']} PASS, {$stats['batch_fail']} FAIL");

        $totalTests = $stats['total'] * 3;
        $totalPass = $stats['preview_pass'] + $stats['pdf_pass'] + $stats['batch_pass'];
        $healthScore = ($totalPass / $totalTests) * 100;

        $this->newLine();
        $this->info("System Health Score: " . number_format($healthScore, 2) . "%");

        if ($healthScore === 100) {
            $this->info("✅ SYSTEM FULLY OPERATIONAL");
            return 0;
        } elseif ($healthScore >= 90) {
            $this->warn("⚠️  SYSTEM OPERATIONAL WITH WARNINGS");
            return 0;
        } else {
            $this->error("❌ SYSTEM HAS CRITICAL ISSUES");
            return 1;
        }
    }
}
