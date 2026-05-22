<?php

namespace App\Console\Commands;

use App\Services\Compliance\Diagnostics\ComplianceDiagnosticEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunComplianceDiagnostics extends Command
{
    protected $signature = 'compliance:diagnose {--save : Save report to storage}';
    protected $description = 'Run comprehensive compliance system diagnostics';

    public function __construct(private ComplianceDiagnosticEngine $diagnosticEngine) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Running Compliance System Diagnostics...');
        $this->newLine();

        $report = $this->diagnosticEngine->runFullDiagnostics();

        // Display health score
        $this->line('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('SYSTEM HEALTH SCORE: ' . $report['health_score'] . '%');
        $this->info('STATUS: ' . strtoupper($report['status']));
        $this->info('═══════════════════════════════════════════════════════════');
        $this->line('');

        // Display summary
        $this->info('Component Summary:');
        $this->line('  ✓ Passed: ' . $report['summary']['components_passed']);
        $this->line('  ✗ Failed: ' . $report['summary']['components_failed']);
        $this->line('  ⚠ Issues: ' . $report['summary']['total_issues']);
        $this->line('');

        // Display component status
        $this->info('Component Diagnostics:');
        $this->table(
            ['Component', 'Status', 'Weight'],
            array_map(fn($name, $data) => [
                ucfirst(str_replace('_', ' ', $name)),
                $data['status'] === 'pass' ? '✓ PASS' : '✗ FAIL',
                $data['weight'] . '%'
            ], array_keys($report['diagnostics']), $report['diagnostics'])
        );
        $this->line('');

        // Display root causes
        if (!empty($report['root_causes'])) {
            $this->warn('Root Cause Analysis:');
            foreach ($report['root_causes'] as $index => $cause) {
                $this->line('');
                $this->line(($index + 1) . '. ' . $cause['component']);
                $this->line('   Root Cause: ' . ($cause['root_cause'] ?? $cause['issue'] ?? 'Unknown'));
                $this->line('   Recommendation: ' . $cause['recommended_fix']);
                if (isset($cause['affected_files'])) {
                    $this->line('   Affected Files: ' . implode(', ', $cause['affected_files']));
                }
            }
            $this->line('');
        }

        // Display execution time
        $this->info('Execution Time: ' . $report['execution_time'] . 'ms');
        $this->info('Timestamp: ' . $report['timestamp']);

        // Save report if requested
        if ($this->option('save')) {
            $filename = 'diagnostic_report_' . now()->format('Y-m-d_H-i-s') . '.json';
            Storage::disk('local')->put("logs/{$filename}", json_encode($report, JSON_PRETTY_PRINT));
            $this->info('Report saved to: storage/logs/' . $filename);
        }

        return $report['health_score'] === 100 ? 0 : 1;
    }
}
