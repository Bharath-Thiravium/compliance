<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Compliance\Registry\FormRegistry;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class FormDataDiagnosticReport extends Command
{
    protected $signature = 'compliance:diagnostic-report {--tenant_id=1} {--branch_id=1} {--output=}';
    protected $description = 'Generate comprehensive diagnostic report for form data issues';

    private array $diagnostics = [];

    public function handle()
    {
        $tenantId = (int)$this->option('tenant_id');
        $branchId = (int)$this->option('branch_id');

        $this->info('=== FORM DATA DIAGNOSTIC REPORT ===');
        $this->newLine();

        // Run diagnostics
        $this->runDiagnostics($tenantId, $branchId);

        // Generate report
        $report = $this->generateDetailedReport($tenantId, $branchId);

        // Save report
        $outputPath = $this->option('output') ?? storage_path('logs/form_diagnostic_report_' . now()->format('Y-m-d_H-i-s') . '.md');
        file_put_contents($outputPath, $report);

        $this->info("Diagnostic report saved to: {$outputPath}");
        $this->newLine();

        // Display summary
        $this->displaySummary();

        return 0;
    }

    private function runDiagnostics(int $tenantId, int $branchId): void
    {
        $this->line('Running diagnostics...');

        // Check tenant setup
        $this->diagnostics['tenant'] = $this->checkTenantSetup($tenantId);

        // Check branch setup
        $this->diagnostics['branch'] = $this->checkBranchSetup($branchId, $tenantId);

        // Check data availability
        $this->diagnostics['data'] = $this->checkDataAvailability($tenantId, $branchId);

        // Check form registry
        $this->diagnostics['registry'] = $this->checkFormRegistry();

        // Check API services
        $this->diagnostics['api_services'] = $this->checkApiServices();

        // Check generators
        $this->diagnostics['generators'] = $this->checkGenerators();

        // Check blade templates
        $this->diagnostics['templates'] = $this->checkBladeTemplates();

        $this->info('Diagnostics complete.');
    }

    private function checkTenantSetup(int $tenantId): array
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        if (!$tenant) {
            return ['status' => 'FAIL', 'message' => "Tenant {$tenantId} not found"];
        }

        $issues = [];
        if (!$tenant->name) $issues[] = 'Missing tenant name';
        if (!$tenant->pf_code) $issues[] = 'Missing PF code';
        if (!$tenant->esi_code) $issues[] = 'Missing ESI code';

        return [
            'status' => empty($issues) ? 'OK' : 'WARNING',
            'tenant_id' => $tenantId,
            'tenant_name' => $tenant->name,
            'issues' => $issues,
        ];
    }

    private function checkBranchSetup(int $branchId, int $tenantId): array
    {
        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$branch) {
            return ['status' => 'FAIL', 'message' => "Branch {$branchId} not found"];
        }

        $issues = [];
        if (!$branch->branch_name && !$branch->unit_name) $issues[] = 'Missing branch name';
        if (!$branch->address) $issues[] = 'Missing address';

        return [
            'status' => empty($issues) ? 'OK' : 'WARNING',
            'branch_id' => $branchId,
            'branch_name' => $branch->branch_name ?? $branch->unit_name,
            'issues' => $issues,
        ];
    }

    private function checkDataAvailability(int $tenantId, int $branchId): array
    {
        return [
            'employees' => DB::table('workforce_employee')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->count(),
            'payroll_entries' => DB::table('workforce_payroll_entry')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->count(),
            'attendance_records' => DB::table('workforce_attendance')
                ->where('tenant_id', $tenantId)
                ->count(),
            'contractors' => DB::table('contractors')
                ->where('tenant_id', $tenantId)
                ->count(),
            'contract_labour' => DB::table('contract_labour_deployment')
                ->where('tenant_id', $tenantId)
                ->count(),
            'deductions' => DB::table('workforce_deductions')
                ->where('tenant_id', $tenantId)
                ->count(),
            'fines' => DB::table('workforce_fines')
                ->where('tenant_id', $tenantId)
                ->count(),
            'advances' => DB::table('workforce_advances')
                ->where('tenant_id', $tenantId)
                ->count(),
        ];
    }

    private function checkFormRegistry(): array
    {
        $registry = FormRegistry::all();
        $registered = count($registry);
        $withBuilders = 0;
        $withTemplates = 0;

        foreach ($registry as $formCode => $config) {
            if (isset($config['builder']) && class_exists($config['builder'])) {
                $withBuilders++;
            }
            if (isset($config['template'])) {
                $withTemplates++;
            }
        }

        return [
            'total_registered' => $registered,
            'with_builders' => $withBuilders,
            'with_templates' => $withTemplates,
            'forms' => array_keys($registry),
        ];
    }

    private function checkApiServices(): array
    {
        $registry = FormRegistry::all();
        $results = [];

        foreach (array_keys($registry) as $formCode) {
            $service = FormApiServiceFactory::make($formCode);
            $results[$formCode] = [
                'has_service' => $service !== null,
                'service_class' => $service ? get_class($service) : null,
            ];
        }

        return $results;
    }

    private function checkGenerators(): array
    {
        $registry = FormRegistry::all();
        $results = [];

        foreach (array_keys($registry) as $formCode) {
            $generator = FormGeneratorFactory::make($formCode);
            $results[$formCode] = [
                'has_generator' => $generator !== null,
                'generator_class' => $generator ? get_class($generator) : null,
            ];
        }

        return $results;
    }

    private function checkBladeTemplates(): array
    {
        $registry = FormRegistry::all();
        $results = [];
        $missing = [];

        foreach ($registry as $formCode => $config) {
            $template = $config['template'] ?? null;
            $exists = $template && View::exists($template);
            $results[$formCode] = [
                'template' => $template,
                'exists' => $exists,
            ];

            if (!$exists) {
                $missing[] = $formCode;
            }
        }

        return [
            'total_templates' => count($results),
            'existing' => count($results) - count($missing),
            'missing' => $missing,
            'details' => $results,
        ];
    }

    private function generateDetailedReport(int $tenantId, int $branchId): string
    {
        $report = "# FORM DATA DIAGNOSTIC REPORT\n\n";
        $report .= "**Generated:** " . now()->toDateTimeString() . "\n";
        $report .= "**Tenant ID:** {$tenantId}\n";
        $report .= "**Branch ID:** {$branchId}\n\n";

        // Tenant Setup
        $report .= "## 1. TENANT SETUP\n\n";
        $tenantDiag = $this->diagnostics['tenant'];
        $report .= "**Status:** " . ($tenantDiag['status'] === 'OK' ? '✅ OK' : '⚠️ ' . $tenantDiag['status']) . "\n";
        if (isset($tenantDiag['tenant_name'])) {
            $report .= "**Name:** {$tenantDiag['tenant_name']}\n";
        }
        if (!empty($tenantDiag['issues'])) {
            $report .= "**Issues:**\n";
            foreach ($tenantDiag['issues'] as $issue) {
                $report .= "- {$issue}\n";
            }
        }
        $report .= "\n";

        // Branch Setup
        $report .= "## 2. BRANCH SETUP\n\n";
        $branchDiag = $this->diagnostics['branch'];
        $report .= "**Status:** " . ($branchDiag['status'] === 'OK' ? '✅ OK' : '⚠️ ' . $branchDiag['status']) . "\n";
        if (isset($branchDiag['branch_name'])) {
            $report .= "**Name:** {$branchDiag['branch_name']}\n";
        }
        if (!empty($branchDiag['issues'])) {
            $report .= "**Issues:**\n";
            foreach ($branchDiag['issues'] as $issue) {
                $report .= "- {$issue}\n";
            }
        }
        $report .= "\n";

        // Data Availability
        $report .= "## 3. DATA AVAILABILITY\n\n";
        $dataDiag = $this->diagnostics['data'];
        $report .= "| Dataset | Count |\n";
        $report .= "|---------|-------|\n";
        foreach ($dataDiag as $dataset => $count) {
            $status = $count > 0 ? '✅' : '❌';
            $report .= "| {$dataset} | {$status} {$count} |\n";
        }
        $report .= "\n";

        // Form Registry
        $report .= "## 4. FORM REGISTRY\n\n";
        $regDiag = $this->diagnostics['registry'];
        $report .= "- **Total Registered:** {$regDiag['total_registered']}\n";
        $report .= "- **With Builders:** {$regDiag['with_builders']}\n";
        $report .= "- **With Templates:** {$regDiag['with_templates']}\n";
        $report .= "\n";

        // API Services
        $report .= "## 5. API SERVICES\n\n";
        $apiDiag = $this->diagnostics['api_services'];
        $withService = collect($apiDiag)->where('has_service', true)->count();
        $report .= "- **Forms with API Services:** {$withService} / " . count($apiDiag) . "\n";
        $report .= "- **Forms without API Services:**\n";
        foreach ($apiDiag as $formCode => $info) {
            if (!$info['has_service']) {
                $report .= "  - {$formCode}\n";
            }
        }
        $report .= "\n";

        // Generators
        $report .= "## 6. GENERATORS\n\n";
        $genDiag = $this->diagnostics['generators'];
        $withGen = collect($genDiag)->where('has_generator', true)->count();
        $report .= "- **Forms with Generators:** {$withGen} / " . count($genDiag) . "\n";
        $report .= "- **Forms without Generators:**\n";
        foreach ($genDiag as $formCode => $info) {
            if (!$info['has_generator']) {
                $report .= "  - {$formCode}\n";
            }
        }
        $report .= "\n";

        // Blade Templates
        $report .= "## 7. BLADE TEMPLATES\n\n";
        $templateDiag = $this->diagnostics['templates'];
        $report .= "- **Total Templates:** {$templateDiag['total_templates']}\n";
        $report .= "- **Existing:** {$templateDiag['existing']}\n";
        $report .= "- **Missing:** " . count($templateDiag['missing']) . "\n";
        if (!empty($templateDiag['missing'])) {
            $report .= "- **Missing Templates:**\n";
            foreach ($templateDiag['missing'] as $formCode) {
                $report .= "  - {$formCode}\n";
            }
        }
        $report .= "\n";

        // Recommendations
        $report .= "## 8. RECOMMENDATIONS\n\n";
        $report .= $this->generateRecommendations();

        return $report;
    }

    private function generateRecommendations(): string
    {
        $recommendations = "";

        // Check for missing data
        $dataDiag = $this->diagnostics['data'];
        if ($dataDiag['employees'] === 0) {
            $recommendations .= "### ⚠️ No Employees Found\n";
            $recommendations .= "**Action:** Seed employee data using:\n";
            $recommendations .= "```bash\nphp artisan compliance:generate-demo-dataset --tenant_id=1 --branch_id=1 --employees=20\n```\n\n";
        }

        if ($dataDiag['payroll_entries'] === 0 && $dataDiag['employees'] > 0) {
            $recommendations .= "### ⚠️ No Payroll Entries Found\n";
            $recommendations .= "**Action:** Generate payroll entries for employees.\n\n";
        }

        // Check for missing templates
        $templateDiag = $this->diagnostics['templates'];
        if (!empty($templateDiag['missing'])) {
            $recommendations .= "### ⚠️ Missing Blade Templates\n";
            $recommendations .= "**Forms:** " . implode(', ', $templateDiag['missing']) . "\n";
            $recommendations .= "**Action:** Create blade templates in `resources/views/compliance/forms/`\n\n";
        }

        // Check for forms without generators
        $genDiag = $this->diagnostics['generators'];
        $noGen = collect($genDiag)->filter(fn($g) => !$g['has_generator'])->keys()->toArray();
        if (!empty($noGen)) {
            $recommendations .= "### ⚠️ Forms Without Generators\n";
            $recommendations .= "**Forms:** " . implode(', ', $noGen) . "\n";
            $recommendations .= "**Action:** Register generators in FormGeneratorFactory\n\n";
        }

        // Check for forms without API services
        $apiDiag = $this->diagnostics['api_services'];
        $noApi = collect($apiDiag)->filter(fn($a) => !$a['has_service'])->keys()->toArray();
        if (!empty($noApi)) {
            $recommendations .= "### ℹ️ Forms Using Aggregator (No API Service)\n";
            $recommendations .= "**Forms:** " . implode(', ', $noApi) . "\n";
            $recommendations .= "**Note:** These forms fall back to FormDataAggregator\n\n";
        }

        if (empty($recommendations)) {
            $recommendations .= "✅ **All systems operational.** No critical issues detected.\n\n";
        }

        return $recommendations;
    }

    private function displaySummary(): void
    {
        $this->info('=== DIAGNOSTIC SUMMARY ===');
        $this->newLine();

        $dataDiag = $this->diagnostics['data'];
        $this->line("Data Availability:");
        $this->line("  Employees: {$dataDiag['employees']}");
        $this->line("  Payroll Entries: {$dataDiag['payroll_entries']}");
        $this->line("  Attendance Records: {$dataDiag['attendance_records']}");
        $this->newLine();

        $regDiag = $this->diagnostics['registry'];
        $this->line("Form Registry:");
        $this->line("  Total Forms: {$regDiag['total_registered']}");
        $this->line("  With Builders: {$regDiag['with_builders']}");
        $this->line("  With Templates: {$regDiag['with_templates']}");
        $this->newLine();

        $templateDiag = $this->diagnostics['templates'];
        if (!empty($templateDiag['missing'])) {
            $this->line("<error>Missing Templates: " . count($templateDiag['missing']) . "</error>");
        }
    }
}
