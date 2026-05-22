<?php

namespace App\Console\Commands;

use App\Services\Compliance\Testing\ComplianceTestAnalyzer;
use App\Services\Compliance\Testing\ComplianceAutoFixer;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Console\Command;

class FixCompliancePlatform extends Command
{
    protected $signature = 'compliance:fix-platform';
    protected $description = 'Automatically fix compliance platform issues';

    public function handle()
    {
        $this->info('Starting automated compliance platform fixes...');

        $fixer = new ComplianceAutoFixer();
        $fixes = $fixer->fixAllIssues();

        $this->info('Applied fixes:');
        foreach ($fixes as $category => $items) {
            $this->line("  $category:");
            foreach ($items as $item) {
                $this->line("    - $item");
            }
        }

        $this->info('Re-running test analyzer...');
        $orchestrator = app(ComplianceOrchestrator::class);
        $analyzer = new ComplianceTestAnalyzer($orchestrator);
        $report = $analyzer->runFullAnalysis();

        $this->info('Test Results:');
        $this->line("  Health Score: {$report['health_score']}%");
        $this->line("  Errors: " . count($report['errors']));
        $this->line("  Warnings: " . count($report['warnings']));

        if (count($report['errors']) > 0) {
            $this->error('Errors:');
            foreach ($report['errors'] as $error) {
                $this->line("    - $error");
            }
        }

        if (count($report['warnings']) > 0) {
            $this->warn('Warnings:');
            foreach ($report['warnings'] as $warning) {
                $this->line("    - $warning");
            }
        }

        $this->info('Platform fixes complete!');
        return 0;
    }
}
