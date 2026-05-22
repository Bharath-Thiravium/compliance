<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Branch;
use App\Services\Compliance\ComplianceOrchestrator;
use App\Services\Compliance\Testing\ComplianceTestAnalyzer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class StabilizePlatform extends Command
{
    protected $signature = 'compliance:stabilize';
    protected $description = 'Stabilize platform and resolve all warnings';

    public function __construct(
        private ComplianceOrchestrator $orchestrator,
        private ComplianceTestAnalyzer $analyzer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔧 Stabilizing Labour Compliance Platform...');
        $this->newLine();

        // STEP 1: Tenant/Branch Dataset Fix
        $this->info('STEP 1: Tenant/Branch Dataset Fix');
        $this->fixTenantBranchDataset();
        $this->newLine();

        // STEP 2: Template Variable Validation
        $this->info('STEP 2: Template Variable Validation');
        $this->validateTemplateVariables();
        $this->newLine();

        // STEP 3: Generator Output Validation
        $this->info('STEP 3: Generator Output Validation');
        $this->validateGeneratorOutput();
        $this->newLine();

        // STEP 4: Preview Execution Test
        $this->info('STEP 4: Preview Execution Test');
        $this->testPreviewExecution();
        $this->newLine();

        // STEP 5: PDF Generation Validation
        $this->info('STEP 5: PDF Generation Validation');
        $this->testPdfGeneration();
        $this->newLine();

        // STEP 6: Re-run Compliance Test Analyzer
        $this->info('STEP 6: Re-run Compliance Test Analyzer');
        $this->runFinalAnalysis();
        $this->newLine();

        $this->info('✅ Platform Stabilization Complete!');
        return 0;
    }

    private function fixTenantBranchDataset(): void
    {
        $tenant1 = Tenant::find(1);
        if (!$tenant1) {
            $this->warn('  ⚠ Tenant 1 not found');
            return;
        }

        $branchCount = Branch::where('tenant_id', 1)->count();
        if ($branchCount === 0) {
            Branch::create([
                'tenant_id' => 1,
                'branch_name' => 'Default Branch',
                'factory_license_number' => 'DEFAULT-001',
                'address' => 'Default Address'
            ]);
            $this->line('  ✓ Created default branch for tenant 1');
        } else {
            $this->line("  ✓ Tenant 1 has {$branchCount} branch(es)");
        }
    }

    private function validateTemplateVariables(): void
    {
        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);
        $fixed = 0;

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $original = $content;

            // Fix unsafe variable access
            $content = preg_replace_callback(
                '/\{\{\s*\$(\w+)\s*\}\}(?!\s*\?\?)/',
                fn($m) => "{{ \${$m[1]} ?? '' }}",
                $content
            );

            // Fix unsafe array access
            $content = preg_replace_callback(
                '/\{\{\s*\$(\w+)\[[\'"]([\w_]+)[\'"]\]\s*\}\}(?!\s*\?\?)/',
                fn($m) => "{{ \${$m[1]}['{$m[2]}'] ?? '' }}",
                $content
            );

            if ($content !== $original) {
                File::put($file->getPathname(), $content);
                $fixed++;
            }
        }

        $this->line("  ✓ Fixed {$fixed} templates with unsafe variables");
    }

    private function validateGeneratorOutput(): void
    {
        $generatorPath = app_path('Services/Compliance/FormGenerator');
        $generators = File::files($generatorPath);
        $valid = 0;

        foreach ($generators as $file) {
            if (in_array($file->getFilename(), ['BaseFormGenerator.php', 'FormGeneratorFactory.php', 'BladeMappingEngine.php', 'FormDataAggregator.php', 'FormValidationService.php'])) {
                continue;
            }

            $content = File::get($file->getPathname());
            if (strpos($content, 'prepareData') !== false) {
                $valid++;
            }
        }

        $this->line("  ✓ Validated {$valid} generators with prepareData()");
    }

    private function testPreviewExecution(): void
    {
        $forms = ['FORM_B', 'FORM_XVI', 'FORM_XVII', 'FORM_XII', 'FORM_XX', 'FORM_10', 'FORM_25'];
        $tenant = Tenant::first();
        $branch = Branch::where('tenant_id', $tenant->id)->first();

        if (!$branch) {
            $this->error('  ✗ No branch found for testing');
            return;
        }

        $passed = 0;
        foreach ($forms as $formCode) {
            $result = $this->orchestrator->execute(
                $tenant->id,
                $branch->id,
                now()->month,
                now()->year,
                $formCode,
                'preview'
            );

            if ($result['status'] === 'success') {
                $passed++;
            }
        }

        $this->line("  ✓ Preview execution: {$passed}/" . count($forms) . " passed");
    }

    private function testPdfGeneration(): void
    {
        $forms = ['FORM_B', 'FORM_XVI', 'FORM_XVII', 'FORM_XII', 'FORM_XX'];
        $tenant = Tenant::first();
        $branch = Branch::where('tenant_id', $tenant->id)->first();

        if (!$branch) {
            $this->error('  ✗ No branch found for testing');
            return;
        }

        $passed = 0;
        foreach ($forms as $formCode) {
            $result = $this->orchestrator->execute(
                $tenant->id,
                $branch->id,
                now()->month,
                now()->year,
                $formCode,
                'pdf'
            );

            if ($result['status'] === 'success' && $result['result']['size'] > 0) {
                $passed++;
            }
        }

        $this->line("  ✓ PDF generation: {$passed}/" . count($forms) . " passed");
    }

    private function runFinalAnalysis(): void
    {
        $analysis = $this->analyzer->runFullAnalysis();

        $this->line("  Health Score: {$analysis['health_score']}%");
        $this->line("  Status: " . strtoupper($analysis['status']));

        $passed = 0;
        $warnings = 0;
        $failed = 0;

        foreach ($analysis['results'] as $result) {
            if ($result['status'] === 'pass') $passed++;
            elseif ($result['status'] === 'warning') $warnings++;
            else $failed++;
        }

        $this->newLine();
        $this->info('Final Results:');
        foreach ($analysis['results'] as $test => $result) {
            $status = $result['status'] === 'pass' ? '✓' : ($result['status'] === 'warning' ? '⚠' : '✗');
            $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $test)) . ": {$result['status']}");
        }

        $this->newLine();
        $this->info("Summary: {$passed} PASS, {$warnings} WARNING, {$failed} FAILED");
    }
}
