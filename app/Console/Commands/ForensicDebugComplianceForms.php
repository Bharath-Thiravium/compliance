<?php

namespace App\Console\Commands;

use App\Services\Compliance\ForensicDebugger;
use Illuminate\Console\Command;

class ForensicDebugComplianceForms extends Command
{
    protected $signature = 'compliance:forensic-debug {--form= : Specific form code} {--tenant=1 : Tenant ID} {--branch=1 : Branch ID} {--month=1 : Month} {--year=2024 : Year}';
    protected $description = 'Run forensic debugging on compliance forms';

    public function handle()
    {
        $formCode = $this->option('form');
        $tenantId = (int)$this->option('tenant');
        $branchId = (int)$this->option('branch');
        $month = (int)$this->option('month');
        $year = (int)$this->option('year');

        $debugger = new ForensicDebugger();

        if ($formCode) {
            // Debug single form
            $this->info("Debugging form: $formCode");
            $trace = $debugger->traceForm($formCode, $tenantId, $branchId, $month, $year);
            $this->line($debugger->printTrace());
            $this->outputJson($trace);
        } else {
            // Debug all failing forms
            $failingForms = [
                'FORM_2', 'HAZARD_REG', 'FORM_26A', 'FORM_26', 'FORM_8', 'FORM_18', 'FORM_17',
                'FORM_XIX', 'FORM_XIV', 'SHOPS_FORM_VI', 'SHOPS_FINES', 'SHOPS_FORM_13',
                'SHOPS_FORM_12', 'SHOPS_FORM_C', 'SHOPS_UNPAID', 'ESI_FORM_12', 'EPF_INSPECTION'
            ];

            $results = [];
            foreach ($failingForms as $form) {
                $this->info("Debugging: $form");
                $trace = $debugger->traceForm($form, $tenantId, $branchId, $month, $year);
                $results[$form] = $trace;
            }

            $this->outputReport($results);
        }
    }

    private function outputJson(array $trace): void
    {
        $this->line("\n=== JSON OUTPUT ===");
        $this->line(json_encode($trace, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function outputReport(array $results): void
    {
        $this->line("\n=== FORENSIC DEBUG REPORT ===\n");

        $summary = [
            'total_forms' => count($results),
            'api_success' => 0,
            'api_failed' => 0,
            'generator_success' => 0,
            'generator_failed' => 0,
            'template_found' => 0,
            'template_missing' => 0,
            'pipeline_success' => 0,
            'pipeline_failed' => 0,
            'forms_with_records' => 0,
            'forms_without_records' => 0,
        ];

        foreach ($results as $formCode => $trace) {
            $apiStep = $trace['steps'][0] ?? null;
            $generatorStep = $trace['steps'][1] ?? null;
            $templateStep = $trace['steps'][2] ?? null;
            $pipelineStep = $trace['steps'][3] ?? null;

            if ($apiStep['status'] === 'success') {
                $summary['api_success']++;
                if ($apiStep['record_count'] > 0) {
                    $summary['forms_with_records']++;
                } else {
                    $summary['forms_without_records']++;
                }
            } else {
                $summary['api_failed']++;
            }

            if ($generatorStep['status'] === 'success') {
                $summary['generator_success']++;
            } else {
                $summary['generator_failed']++;
            }

            if ($templateStep['status'] === 'exists') {
                $summary['template_found']++;
            } else {
                $summary['template_missing']++;
            }

            if ($pipelineStep['status'] === 'success') {
                $summary['pipeline_success']++;
            } else {
                $summary['pipeline_failed']++;
            }
        }

        $this->table(['Metric', 'Value'], [
            ['Total Forms', $summary['total_forms']],
            ['API Success', $summary['api_success']],
            ['API Failed', $summary['api_failed']],
            ['Generator Success', $summary['generator_success']],
            ['Generator Failed', $summary['generator_failed']],
            ['Template Found', $summary['template_found']],
            ['Template Missing', $summary['template_missing']],
            ['Pipeline Success', $summary['pipeline_success']],
            ['Pipeline Failed', $summary['pipeline_failed']],
            ['Forms with Records', $summary['forms_with_records']],
            ['Forms without Records', $summary['forms_without_records']],
        ]);

        $this->line("\n=== DETAILED RESULTS ===\n");

        foreach ($results as $formCode => $trace) {
            $this->line("Form: $formCode");
            
            foreach ($trace['steps'] as $step) {
                $status = $step['status'];
                $statusColor = $status === 'success' || $status === 'exists' ? 'info' : 'error';
                $this->line("  {$step['name']}: <$statusColor>$status</$statusColor>");

                if ($step['name'] === 'API Service' && $step['status'] === 'success') {
                    $this->line("    Records: {$step['record_count']}");
                }

                if ($step['name'] === 'Generator' && $step['status'] === 'success') {
                    $this->line("    Rows: {$step['row_count']}");
                    if (!empty($step['header_keys'])) {
                        $this->line("    Header: " . implode(', ', $step['header_keys']));
                    }
                }

                if ($step['name'] === 'Full Pipeline' && $step['status'] === 'success') {
                    if (!empty($step['missing_variables'])) {
                        $this->line("    <error>Missing Variables: " . implode(', ', $step['missing_variables']) . "</error>");
                    }
                }

                if (isset($step['error'])) {
                    $this->line("    <error>Error: {$step['error']}</error>");
                }
            }

            $this->line("");
        }

        // Save full report to file
        $reportPath = storage_path('logs/forensic_debug_' . now()->format('Y-m-d_H-i-s') . '.json');
        file_put_contents($reportPath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("Full report saved to: $reportPath");
    }
}
