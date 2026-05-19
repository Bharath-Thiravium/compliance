<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\ComplianceOrchestrator;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ComplianceFormsMaster;

class ComplianceOrchestratorTest extends Command
{
    protected $signature = 'compliance:orchestrator-test {--tenant-id=1} {--branch-id=1} {--month=} {--year=} {--form-code=} {--mode=preview}';
    protected $description = 'Test the compliance orchestrator with multiple forms';

    public function __construct(
        private ComplianceOrchestrator $orchestrator
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $tenantId = (int)$this->option('tenant-id');
        $branchId = (int)$this->option('branch-id');
        $month = (int)($this->option('month') ?? now()->month);
        $year = (int)($this->option('year') ?? now()->year);
        $formCode = $this->option('form-code');
        $mode = $this->option('mode');

        // Validate tenant and branch
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant {$tenantId} not found");
            return 1;
        }

        $branch = Branch::where('id', $branchId)->where('tenant_id', $tenantId)->first();
        if (!$branch) {
            $this->error("Branch {$branchId} not found for tenant {$tenantId}");
            return 1;
        }

        $this->info("=== Compliance Orchestrator Test ===");
        $this->info("Tenant: {$tenant->name} (ID: {$tenantId})");
        $this->info("Branch: {$branch->unit_name ?? $branch->branch_name} (ID: {$branchId})");
        $this->info("Period: {$month}/{$year}");
        $this->info("Mode: {$mode}");
        $this->newLine();

        // Get forms to test
        if ($formCode) {
            $forms = ComplianceFormsMaster::where('form_code', $formCode)->get();
        } else {
            $forms = ComplianceFormsMaster::where('is_active', true)->limit(5)->get();
        }

        if ($forms->isEmpty()) {
            $this->warn("No forms found to test");
            return 1;
        }

        $this->info("Testing " . $forms->count() . " form(s)...");
        $this->newLine();

        $results = [];
        $bar = $this->output->createProgressBar($forms->count());

        foreach ($forms as $form) {
            $bar->advance();

            $result = $this->orchestrator->execute(
                $tenantId,
                $branchId,
                $month,
                $year,
                $form->form_code,
                $mode
            );

            $results[$form->form_code] = $result;
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults($results);

        return 0;
    }

    private function displayResults(array $results)
    {
        $this->info("=== Execution Results ===");
        $this->newLine();

        $table = [];
        $successCount = 0;
        $failureCount = 0;
        $totalTime = 0;
        $totalRecords = 0;

        foreach ($results as $formCode => $result) {
            $status = $result['status'] === 'success' ? '<fg=green>✓ Success</>' : '<fg=red>✗ Failed</>';
            $time = $result['execution_time'] ?? 0;
            $records = $result['records_generated'] ?? 0;
            $error = $result['error'] ?? '';

            $table[] = [
                $formCode,
                $status,
                $time . 'ms',
                $records,
                $error ? substr($error, 0, 40) . '...' : ''
            ];

            if ($result['status'] === 'success') {
                $successCount++;
                $totalTime += $time;
                $totalRecords += $records;
            } else {
                $failureCount++;
            }
        }

        $this->table(
            ['Form Code', 'Status', 'Time', 'Records', 'Error'],
            $table
        );

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Successful: <fg=green>{$successCount}</>");
        $this->line("  Failed: <fg=red>{$failureCount}</>");
        $this->line("  Total Execution Time: {$totalTime}ms");
        $this->line("  Total Records Generated: {$totalRecords}");

        if ($successCount > 0) {
            $avgTime = (int)($totalTime / $successCount);
            $this->line("  Average Time per Form: {$avgTime}ms");
        }
    }
}
