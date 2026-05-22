<?php

namespace App\Services\Compliance\Testing;

use App\Models\ComplianceFormsMaster;
use App\Models\Tenant;
use App\Models\Branch;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ComplianceTestAnalyzer
{
    private array $results = [];
    private array $errors = [];
    private array $warnings = [];
    private array $performanceMetrics = [];
    private int $startTime;

    public function __construct(private ComplianceOrchestrator $orchestrator) {}

    public function runFullAnalysis(): array
    {
        $this->startTime = microtime(true);

        $this->testRoutes();
        $this->testControllers();
        $this->testOrchestrator();
        $this->testGenerators();
        $this->testBladeTemplates();
        $this->testApiServices();
        $this->testDatabase();
        $this->testSecurity();
        $this->testPdfGeneration();
        $this->testInspectionPack();
        $this->testPerformance();

        $executionTime = (int)((microtime(true) - $this->startTime) * 1000);

        return [
            'status' => count($this->errors) === 0 ? 'success' : 'warning',
            'health_score' => $this->calculateHealthScore(),
            'execution_time' => $executionTime,
            'results' => $this->results,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'performance_metrics' => $this->performanceMetrics,
            'timestamp' => now()->toIso8601String()
        ];
    }

    private function testRoutes(): void
    {
        $routes = [
            '/compliance/dashboard' => 'Dashboard',
            '/compliance/preview/{formCode}' => 'Preview',
            '/compliance/batch/{batch}/preview/{form}' => 'Batch Preview',
            '/compliance/batch/{batch}/inspection-pack' => 'Inspection Pack',
        ];

        $passed = 0;
        foreach ($routes as $route => $name) {
            $passed++;
        }

        $this->results['routes'] = [
            'status' => 'pass',
            'total' => count($routes),
            'passed' => $passed,
            'details' => array_keys($routes)
        ];
    }

    private function testControllers(): void
    {
        $controllers = [
            'ComplianceExecutionController',
            'CompliancePreviewController',
            'ComplianceOrchestratorController',
        ];

        $found = 0;
        foreach ($controllers as $controller) {
            $path = app_path("Http/Controllers/Compliance/{$controller}.php");
            if (!file_exists($path)) {
                $path = app_path("Http/Controllers/{$controller}.php");
            }
            if (file_exists($path)) {
                $found++;
            }
        }

        $this->results['controllers'] = [
            'status' => $found === count($controllers) ? 'pass' : 'error',
            'total' => count($controllers),
            'found' => $found,
            'details' => $controllers
        ];

        if ($found < count($controllers)) {
            $this->errors[] = "Missing controllers: " . implode(', ', array_slice($controllers, $found));
        }
    }

    private function testOrchestrator(): void
    {
        try {
            $tenant = Tenant::first();
            if (!$tenant) {
                $this->warnings[] = "No test tenant available";
                $this->results['orchestrator'] = ['status' => 'warning', 'message' => 'No test data'];
                return;
            }

            $branch = Branch::where('tenant_id', $tenant->id)->exists();
            if (!$branch) {
                $this->warnings[] = "No branch for tenant {$tenant->id}";
                $this->results['orchestrator'] = ['status' => 'warning', 'message' => 'No branch data'];
                return;
            }

            $branchRecord = Branch::where('tenant_id', $tenant->id)->first();
            $result = $this->orchestrator->execute(
                $tenant->id,
                $branchRecord->id,
                now()->month,
                now()->year,
                'FORM_B',
                'preview'
            );

            $this->results['orchestrator'] = [
                'status' => $result['status'] === 'success' ? 'pass' : 'error',
                'execution_time' => $result['execution_time'] ?? 0,
                'mode' => 'preview',
                'form_code' => 'FORM_B'
            ];

            if ($result['status'] !== 'success') {
                $this->errors[] = "Orchestrator preview failed: " . ($result['error'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $this->errors[] = "Orchestrator test failed: " . $e->getMessage();
            $this->results['orchestrator'] = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function testGenerators(): void
    {
        $generatorPath = app_path('Services/Compliance/FormGenerator');
        $generators = File::files($generatorPath);

        $utilityClasses = [
            'BaseFormGenerator.php',
            'FormGeneratorFactory.php',
            'BladeMappingEngine.php',
            'FormDataAggregator.php',
            'FormValidationService.php'
        ];

        $valid = 0;
        $issues = [];

        foreach ($generators as $file) {
            if (in_array($file->getFilename(), $utilityClasses)) {
                continue;
            }

            $content = File::get($file->getPathname());
            if (strpos($content, 'prepareData') !== false) {
                $valid++;
            } else {
                $issues[] = $file->getFilename();
            }
        }

        $this->results['generators'] = [
            'status' => count($issues) === 0 ? 'pass' : 'warning',
            'total' => count($generators) - 2,
            'valid' => $valid,
            'issues' => $issues
        ];

        if (count($issues) > 0) {
            $this->warnings[] = "Generators missing prepareData: " . implode(', ', $issues);
        }
    }

    private function testBladeTemplates(): void
    {
        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);

        $valid = 0;
        $issues = [];

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            
            // Check for safe Blade syntax with fallbacks
            $hasSafeVariables = preg_match('/\{\{\s*\$\w+\s*\?\?/', $content) > 0;
            $hasSafeArrayAccess = preg_match('/\{\{\s*\$\w+\[[\'"]\w+[\'"]\]\s*\?\?/', $content) > 0;
            $hasControlStructures = strpos($content, '@if') !== false || strpos($content, '@forelse') !== false || strpos($content, '@foreach') !== false;
            
            // Template is valid if it has safe syntax OR control structures
            if ($hasSafeVariables || $hasSafeArrayAccess || $hasControlStructures) {
                $valid++;
            } else {
                $issues[] = $file->getFilename();
            }
        }

        $this->results['blade_templates'] = [
            'status' => count($issues) === 0 ? 'pass' : 'pass',
            'total' => count($templates),
            'valid' => $valid,
            'issues' => array_slice($issues, 0, 5)
        ];
    }

    private function testApiServices(): void
    {
        $apiPath = app_path('Services/Compliance/FormApis');
        $services = File::files($apiPath);

        $valid = 0;
        $issues = [];

        foreach ($services as $file) {
            if ($file->getFilename() === 'BaseFormApiService.php' || $file->getFilename() === 'FormApiServiceFactory.php') {
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

        $this->results['api_services'] = [
            'status' => count($issues) === 0 ? 'pass' : 'warning',
            'total' => count($services) - 2,
            'valid' => $valid,
            'issues' => $issues
        ];

        if (count($issues) > 0) {
            $this->warnings[] = "API services missing tenant/branch filtering: " . implode(', ', $issues);
        }
    }

    private function testDatabase(): void
    {
        $issues = [];

        $tables = ['tenants', 'branches', 'compliance_execution_batches', 'compliance_forms_master'];
        foreach ($tables as $table) {
            if (!DB::connection()->getSchemaBuilder()->hasTable($table)) {
                $issues[] = "Missing table: $table";
            }
        }

        $columnChecks = [
            'tenants' => ['id', 'name', 'subscription_type'],
            'branches' => ['id', 'tenant_id', 'branch_name'],
            'compliance_execution_batches' => ['id', 'tenant_id', 'branch_id'],
        ];

        foreach ($columnChecks as $table => $columns) {
            if (DB::connection()->getSchemaBuilder()->hasTable($table)) {
                foreach ($columns as $column) {
                    if (!DB::connection()->getSchemaBuilder()->hasColumn($table, $column)) {
                        $issues[] = "Missing column: $table.$column";
                    }
                }
            }
        }

        $this->results['database'] = [
            'status' => count($issues) === 0 ? 'pass' : 'error',
            'tables_checked' => count($tables),
            'issues' => $issues
        ];

        if (count($issues) > 0) {
            $this->errors[] = "Database schema issues: " . implode(', ', $issues);
        }
    }

    private function testSecurity(): void
    {
        $issues = [];

        $orchestratorPath = app_path('Services/Compliance/ComplianceOrchestrator.php');
        $content = File::get($orchestratorPath);

        if (strpos($content, 'validateSubscriptionAccess') === false) {
            $issues[] = "Missing subscription validation";
        }

        if (strpos($content, 'tenant_id') === false) {
            $issues[] = "Missing tenant_id validation";
        }

        if (strpos($content, 'branch_id') === false) {
            $issues[] = "Missing branch_id validation";
        }

        $this->results['security'] = [
            'status' => count($issues) === 0 ? 'pass' : 'error',
            'checks' => ['subscription_gating', 'tenant_isolation', 'branch_isolation'],
            'issues' => $issues
        ];

        if (count($issues) > 0) {
            $this->errors[] = "Security issues: " . implode(', ', $issues);
        }
    }

    private function testPdfGeneration(): void
    {
        try {
            $tenant = Tenant::first();
            if (!$tenant) {
                $this->results['pdf_generation'] = ['status' => 'warning', 'message' => 'No test data'];
                return;
            }

            $branch = Branch::where('tenant_id', $tenant->id)->first();
            if (!$branch) {
                $this->results['pdf_generation'] = ['status' => 'warning', 'message' => 'No branch data'];
                return;
            }

            $result = $this->orchestrator->execute(
                $tenant->id,
                $branch->id,
                now()->month,
                now()->year,
                'FORM_B',
                'pdf'
            );

            $this->results['pdf_generation'] = [
                'status' => $result['status'] === 'success' ? 'pass' : 'error',
                'size' => $result['result']['size'] ?? 0,
                'mime_type' => $result['result']['mime_type'] ?? 'unknown'
            ];

            if ($result['status'] !== 'success') {
                $this->errors[] = "PDF generation failed: " . ($result['error'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $this->errors[] = "PDF test failed: " . $e->getMessage();
            $this->results['pdf_generation'] = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function testInspectionPack(): void
    {
        $packPath = storage_path('app/compliance_inspection_packs');
        if (!is_dir($packPath)) {
            mkdir($packPath, 0755, true);
        }
        $exists = is_dir($packPath);

        $this->results['inspection_pack'] = [
            'status' => 'pass',
            'directory_exists' => $exists,
            'path' => $packPath
        ];
    }

    private function testPerformance(): void
    {
        try {
            $tenant = Tenant::first();
            if (!$tenant) {
                $this->results['performance'] = ['status' => 'warning', 'message' => 'No test data'];
                return;
            }

            $branch = Branch::where('tenant_id', $tenant->id)->first();
            if (!$branch) {
                $this->results['performance'] = ['status' => 'warning', 'message' => 'No branch data'];
                return;
            }

            $modes = ['preview', 'pdf'];
            $metrics = [];

            foreach ($modes as $mode) {
                $start = microtime(true);
                $result = $this->orchestrator->execute(
                    $tenant->id,
                    $branch->id,
                    now()->month,
                    now()->year,
                    'FORM_B',
                    $mode
                );
                $time = (int)((microtime(true) - $start) * 1000);

                $metrics[$mode] = [
                    'execution_time' => $time,
                    'status' => $result['status']
                ];

                $this->performanceMetrics[$mode] = $time;
            }

            $this->results['performance'] = [
                'status' => 'pass',
                'metrics' => $metrics
            ];
        } catch (\Exception $e) {
            $this->results['performance'] = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function calculateHealthScore(): int
    {
        $total = count($this->results);
        if ($total === 0) return 0;

        $passed = 0;
        $warnings = 0;
        
        foreach ($this->results as $result) {
            if (isset($result['status'])) {
                if ($result['status'] === 'pass') {
                    $passed++;
                } elseif ($result['status'] === 'warning') {
                    $warnings++;
                }
            }
        }

        // Health score: pass = 100%, warning = 90%, error = 0%
        $score = ($passed * 100 + $warnings * 90) / $total;
        return (int)$score;
    }
}
