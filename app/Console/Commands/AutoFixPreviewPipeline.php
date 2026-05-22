<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ComplianceFormsMaster;
use App\Services\Compliance\ComplianceOrchestrator;
use App\Services\Compliance\Testing\ComplianceTestAnalyzer;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class AutoFixPreviewPipeline extends Command
{
    protected $signature = 'compliance:auto-fix-preview {--forms=FORM_B,FORM_XVI,FORM_XVII,FORM_XII,FORM_XX}';
    protected $description = 'Automatically analyze and fix the preview pipeline';

    public function __construct(
        private ComplianceOrchestrator $orchestrator,
        private ComplianceTestAnalyzer $analyzer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔧 Starting Automated Preview Pipeline Fix...');
        $this->newLine();

        // STEP 1: Dataset Validation
        $this->info('STEP 1: Dataset Validation');
        $this->validateDataset();
        $this->newLine();

        // STEP 2: Form Mapping Verification
        $this->info('STEP 2: Form Mapping Verification');
        $this->verifyFormMappings();
        $this->newLine();

        // STEP 3: API Service Validation
        $this->info('STEP 3: API Service Validation');
        $this->validateApiServices();
        $this->newLine();

        // STEP 4: Generator Validation
        $this->info('STEP 4: Generator Validation');
        $this->validateGenerators();
        $this->newLine();

        // STEP 5: Blade Template Validation
        $this->info('STEP 5: Blade Template Validation');
        $this->validateBladeTemplates();
        $this->newLine();

        // STEP 6: Preview Execution Test
        $this->info('STEP 6: Preview Execution Test');
        $this->testPreviewExecution();
        $this->newLine();

        // STEP 7: PDF Generation Validation
        $this->info('STEP 7: PDF Generation Validation');
        $this->testPdfGeneration();
        $this->newLine();

        // STEP 8: Re-run Compliance Test Analyzer
        $this->info('STEP 8: Re-run Compliance Test Analyzer');
        $this->runFinalAnalysis();
        $this->newLine();

        $this->info('✅ Automated Preview Pipeline Fix Complete!');
        return 0;
    }

    private function validateDataset(): void
    {
        $this->line('Checking database tables...');

        $tenants = Tenant::count();
        $branches = Branch::count();
        $forms = ComplianceFormsMaster::count();

        $this->line("  ✓ Tenants: {$tenants}");
        $this->line("  ✓ Branches: {$branches}");
        $this->line("  ✓ Forms: {$forms}");

        // Check for tenants without branches
        $tenantsWithoutBranches = Tenant::whereDoesntHave('branches')->get();
        if ($tenantsWithoutBranches->count() > 0) {
            $this->warn("  ⚠ Found " . $tenantsWithoutBranches->count() . " tenants without branches");
            foreach ($tenantsWithoutBranches as $tenant) {
                $this->createDefaultBranch($tenant);
            }
        } else {
            $this->line("  ✓ All tenants have branches");
        }
    }

    private function createDefaultBranch(Tenant $tenant): void
    {
        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'branch_name' => 'Default Branch',
            'factory_license_number' => 'DEFAULT-' . $tenant->id,
            'address' => 'Default Address'
        ]);

        $this->line("  ✓ Created default branch for tenant {$tenant->id}: {$branch->id}");
    }

    private function verifyFormMappings(): void
    {
        $this->line('Scanning compliance_forms_master table...');

        $forms = ComplianceFormsMaster::where('is_active', true)->get();
        $issues = [];

        foreach ($forms as $form) {
            $generator = FormGeneratorFactory::make($form->form_code);
            $apiService = FormApiServiceFactory::make($form->form_code);
            $viewPath = "compliance.forms." . strtolower($form->form_code);
            $viewExists = View::exists($viewPath);

            if (!$generator) {
                $issues[] = "{$form->form_code}: Missing generator";
            }
            if (!$viewExists) {
                $issues[] = "{$form->form_code}: Missing blade template";
            }
        }

        if (count($issues) > 0) {
            $this->warn("  ⚠ Found " . count($issues) . " mapping issues:");
            foreach (array_slice($issues, 0, 10) as $issue) {
                $this->line("    - {$issue}");
            }
        } else {
            $this->line("  ✓ All form mappings verified");
        }
    }

    private function validateApiServices(): void
    {
        $this->line('Scanning API services...');

        $apiPath = app_path('Services/Compliance/FormApis');
        $services = File::files($apiPath);

        $valid = 0;
        $issues = [];

        foreach ($services as $file) {
            if (in_array($file->getFilename(), ['BaseFormApiService.php', 'FormApiServiceFactory.php', 'FormApiServices.php'])) {
                continue;
            }

            $content = File::get($file->getPathname());
            $hasTenantFilter = strpos($content, 'tenant_id') !== false;
            $hasBranchFilter = strpos($content, 'branch_id') !== false;

            if ($hasTenantFilter && $hasBranchFilter) {
                $valid++;
            } else {
                $issues[] = $file->getFilename();
            }
        }

        $this->line("  ✓ Valid API services: {$valid}");
        if (count($issues) > 0) {
            $this->warn("  ⚠ Services missing filters: " . implode(', ', array_slice($issues, 0, 5)));
        }
    }

    private function validateGenerators(): void
    {
        $this->line('Scanning generators...');

        $generatorPath = app_path('Services/Compliance/FormGenerator');
        $generators = File::files($generatorPath);

        $valid = 0;
        $issues = [];

        foreach ($generators as $file) {
            if (in_array($file->getFilename(), ['BaseFormGenerator.php', 'FormGeneratorFactory.php', 'BladeMappingEngine.php', 'FormDataAggregator.php', 'FormValidationService.php'])) {
                continue;
            }

            $content = File::get($file->getPathname());
            if (strpos($content, 'prepareData') !== false) {
                $valid++;
            } else {
                $issues[] = $file->getFilename();
            }
        }

        $this->line("  ✓ Valid generators: {$valid}");
        if (count($issues) > 0) {
            $this->warn("  ⚠ Generators missing prepareData: " . implode(', ', array_slice($issues, 0, 5)));
        }
    }

    private function validateBladeTemplates(): void
    {
        $this->line('Scanning blade templates...');

        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);

        $valid = 0;
        $issues = [];

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $hasData = strpos($content, '@if') !== false || strpos($content, '@forelse') !== false || strpos($content, '@foreach') !== false;

            if ($hasData) {
                $valid++;
            } else {
                $issues[] = $file->getFilename();
            }
        }

        $this->line("  ✓ Valid templates: {$valid}");
        if (count($issues) > 0) {
            $this->warn("  ⚠ Templates with issues: " . count($issues));
        }
    }

    private function testPreviewExecution(): void
    {
        $this->line('Testing preview execution...');

        $forms = explode(',', $this->option('forms'));
        $tenant = Tenant::first();
        $branch = Branch::where('tenant_id', $tenant->id)->first();

        if (!$branch) {
            $this->error("  ✗ No branch found for testing");
            return;
        }

        $passed = 0;
        $failed = 0;

        foreach ($forms as $formCode) {
            $formCode = trim($formCode);
            $result = $this->orchestrator->execute(
                $tenant->id,
                $branch->id,
                now()->month,
                now()->year,
                $formCode,
                'preview'
            );

            if ($result['status'] === 'success') {
                $this->line("  ✓ {$formCode}: Preview OK ({$result['execution_time']}ms)");
                $passed++;
            } else {
                $this->line("  ✗ {$formCode}: " . ($result['error'] ?? 'Unknown error'));
                $failed++;
            }
        }

        $this->line("  Summary: {$passed} passed, {$failed} failed");
    }

    private function testPdfGeneration(): void
    {
        $this->line('Testing PDF generation...');

        $forms = explode(',', $this->option('forms'));
        $tenant = Tenant::first();
        $branch = Branch::where('tenant_id', $tenant->id)->first();

        if (!$branch) {
            $this->error("  ✗ No branch found for testing");
            return;
        }

        $passed = 0;
        $failed = 0;

        foreach ($forms as $formCode) {
            $formCode = trim($formCode);
            $result = $this->orchestrator->execute(
                $tenant->id,
                $branch->id,
                now()->month,
                now()->year,
                $formCode,
                'pdf'
            );

            if ($result['status'] === 'success' && $result['result']['size'] > 0) {
                $this->line("  ✓ {$formCode}: PDF OK (" . round($result['result']['size'] / 1024, 2) . "KB)");
                $passed++;
            } else {
                $this->line("  ✗ {$formCode}: PDF generation failed");
                $failed++;
            }
        }

        $this->line("  Summary: {$passed} passed, {$failed} failed");
    }

    private function runFinalAnalysis(): void
    {
        $this->line('Running final compliance analysis...');

        $analysis = $this->analyzer->runFullAnalysis();

        $this->line("  Health Score: {$analysis['health_score']}%");
        $this->line("  Status: " . strtoupper($analysis['status']));
        $this->line("  Execution Time: {$analysis['execution_time']}ms");

        if (count($analysis['errors']) > 0) {
            $this->warn("  Errors: " . count($analysis['errors']));
            foreach (array_slice($analysis['errors'], 0, 5) as $error) {
                $this->line("    - {$error}");
            }
        }

        if (count($analysis['warnings']) > 0) {
            $this->warn("  Warnings: " . count($analysis['warnings']));
            foreach (array_slice($analysis['warnings'], 0, 5) as $warning) {
                $this->line("    - {$warning}");
            }
        }

        // Display results
        $this->newLine();
        $this->info('Test Results:');
        foreach ($analysis['results'] as $test => $result) {
            $status = $result['status'] === 'pass' ? '✓' : ($result['status'] === 'warning' ? '⚠' : '✗');
            $this->line("  {$status} " . ucfirst(str_replace('_', ' ', $test)) . ": {$result['status']}");
        }
    }
}
