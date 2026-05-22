<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\Testing\ComplianceTestAnalyzer;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\File;

class RegenerateDashboardReport extends Command
{
    protected $signature = 'compliance:regenerate-dashboard';
    protected $description = 'Regenerate the dashboard test analysis report';

    public function __construct(
        private ComplianceOrchestrator $orchestrator,
        private ComplianceTestAnalyzer $analyzer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔄 Regenerating Dashboard Test Analysis Report...');
        $this->newLine();

        $analysis = $this->analyzer->runFullAnalysis();

        $this->displayResults($analysis);

        // Save report to file for reference
        $reportPath = storage_path('logs/dashboard_report_' . now()->format('Y-m-d_H-i-s') . '.json');
        File::put($reportPath, json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line("📄 Report saved to: {$reportPath}");

        $this->newLine();
        $this->info('✅ Dashboard Report Regenerated Successfully!');
        return 0;
    }

    private function displayResults(array $analysis): void
    {
        $this->line('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('COMPLIANCE SYSTEM HEALTH REPORT');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->line("Health Score: {$analysis['health_score']}%");
        $this->line("Status: " . strtoupper($analysis['status']));
        $this->line("Execution Time: {$analysis['execution_time']}ms");
        $this->line("Timestamp: {$analysis['timestamp']}");

        $this->newLine();
        $this->info('Test Results:');
        $this->line('───────────────────────────────────────────────────────────');

        $passed = 0;
        $warnings = 0;
        $failed = 0;

        foreach ($analysis['results'] as $test => $result) {
            $status = $result['status'] === 'pass' ? '✓ PASS' : ($result['status'] === 'warning' ? '⚠ WARNING' : '✗ FAIL');
            $testName = ucfirst(str_replace('_', ' ', $test));
            $this->line("  {$status}  {$testName}");

            if ($result['status'] === 'pass') $passed++;
            elseif ($result['status'] === 'warning') $warnings++;
            else $failed++;
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  ✓ Passed:  {$passed}");
        $this->line("  ⚠ Warnings: {$warnings}");
        $this->line("  ✗ Failed:  {$failed}");

        if (count($analysis['errors']) > 0) {
            $this->newLine();
            $this->warn('Errors:');
            foreach ($analysis['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        if (count($analysis['warnings']) > 0) {
            $this->newLine();
            $this->warn('Warnings:');
            foreach (array_slice($analysis['warnings'], 0, 5) as $warning) {
                $this->line("  • {$warning}");
            }
            if (count($analysis['warnings']) > 5) {
                $this->line("  ... and " . (count($analysis['warnings']) - 5) . " more");
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
    }
}
