<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\Testing\ComplianceTestAnalyzer;

class RunFinalComplianceAnalysis extends Command
{
    protected $signature = 'compliance:final-analysis';
    protected $description = 'Run final compliance analysis with detailed reporting';

    public function __construct(private ComplianceTestAnalyzer $analyzer) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('📊 Running Final Compliance Analysis...');
        $this->newLine();

        $analysis = $this->analyzer->runFullAnalysis();

        // Display comprehensive results
        $this->displayResults($analysis);

        return 0;
    }

    private function displayResults(array $analysis): void
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('COMPLIANCE SYSTEM HEALTH REPORT');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Health Score
        $healthScore = $analysis['health_score'];
        $scoreColor = $healthScore >= 85 ? 'info' : ($healthScore >= 70 ? 'comment' : 'error');
        $this->line("<{$scoreColor}>System Health Score: {$healthScore}%</{$scoreColor}>");
        $this->line("Status: " . strtoupper($analysis['status']));
        $this->line("Execution Time: {$analysis['execution_time']}ms");
        $this->newLine();

        // Test Results
        $this->info('TEST RESULTS:');
        $this->info('─────────────────────────────────────────────────────────────');
        
        $passed = 0;
        $warnings = 0;
        $failed = 0;

        foreach ($analysis['results'] as $test => $result) {
            $status = $result['status'];
            $testName = ucfirst(str_replace('_', ' ', $test));

            if ($status === 'pass') {
                $this->line("  <info>✓ {$testName}: PASS</info>");
                $passed++;
            } elseif ($status === 'warning') {
                $this->line("  <comment>⚠ {$testName}: WARNING</comment>");
                $warnings++;
            } else {
                $this->line("  <error>✗ {$testName}: FAILED</error>");
                $failed++;
            }
        }

        $this->newLine();
        $this->info('SUMMARY:');
        $this->info('─────────────────────────────────────────────────────────────');
        $this->line("  Passed:  {$passed}");
        $this->line("  Warnings: {$warnings}");
        $this->line("  Failed:  {$failed}");
        $this->newLine();

        // Errors
        if (count($analysis['errors']) > 0) {
            $this->error('ERRORS:');
            foreach (array_slice($analysis['errors'], 0, 10) as $error) {
                $this->line("  • {$error}");
            }
            $this->newLine();
        }

        // Warnings
        if (count($analysis['warnings']) > 0) {
            $this->warn('WARNINGS:');
            foreach (array_slice($analysis['warnings'], 0, 10) as $warning) {
                $this->line("  • {$warning}");
            }
            $this->newLine();
        }

        // Performance Metrics
        if (count($analysis['performance_metrics']) > 0) {
            $this->info('PERFORMANCE METRICS:');
            $this->info('─────────────────────────────────────────────────────────────');
            foreach ($analysis['performance_metrics'] as $mode => $time) {
                $this->line("  {$mode}: {$time}ms");
            }
            $this->newLine();
        }

        // Final Status
        $this->info('═══════════════════════════════════════════════════════════');
        if ($healthScore >= 85) {
            $this->info('✅ SYSTEM READY FOR PRODUCTION');
        } elseif ($healthScore >= 70) {
            $this->warn('⚠️  SYSTEM OPERATIONAL WITH WARNINGS');
        } else {
            $this->error('❌ SYSTEM REQUIRES ATTENTION');
        }
        $this->info('═══════════════════════════════════════════════════════════');
    }
}
