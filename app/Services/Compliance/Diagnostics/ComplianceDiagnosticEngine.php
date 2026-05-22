<?php

namespace App\Services\Compliance\Diagnostics;

use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ComplianceFormsMaster;
use App\Services\Compliance\ComplianceOrchestrator;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\Registry\FormTemplateRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ComplianceDiagnosticEngine
{
    private array $diagnostics = [];
    private array $rootCauses = [];
    private float $startTime;
    private ?Tenant $testTenant = null;
    private ?Branch $testBranch = null;

    public function __construct(private ComplianceOrchestrator $orchestrator) {}

    public function runFullDiagnostics(): array
    {
        $this->startTime = microtime(true);
        $this->diagnostics = [];
        $this->rootCauses = [];

        if (!$this->setupTestData()) {
            return $this->buildReport();
        }

        $this->testPreviewPipeline();
        $this->testGeneratorAnalysis();
        $this->testBladeTemplateAnalysis();
        $this->testApiServiceAnalysis();
        $this->testDatabaseDatasets();
        $this->testPdfGeneration();
        $this->testInspectionPack();
        $this->testSecurityIsolation();

        $healthScore = $this->calculateHealthScore();

        return $this->buildReport($healthScore);
    }

    private function setupTestData(): bool
    {
        $this->testTenant = Tenant::first();
        if (!$this->testTenant) {
            $this->rootCauses[] = [
                'component' => 'Setup',
                'issue' => 'No test tenant available',
                'severity' => 'critical',
                'affected_files' => ['database'],
                'recommendation' => 'Seed test data using: php artisan db:seed'
            ];
            return false;
        }

        $this->testBranch = Branch::where('tenant_id', $this->testTenant->id)->first();
        if (!$this->testBranch) {
            $this->rootCauses[] = [
                'component' => 'Setup',
                'issue' => 'No test branch for tenant',
                'severity' => 'critical',
                'affected_files' => ['database'],
                'recommendation' => 'Create branch for tenant using seeder'
            ];
            return false;
        }

        return true;
    }

    private function testPreviewPipeline(): void
    {
        $forms = ['FORM_B', 'FORM_XVI', 'FORM_XII'];
        $results = [];
        $failures = [];

        foreach ($forms as $formCode) {
            $startTime = microtime(true);
            try {
                $result = $this->orchestrator->execute(
                    $this->testTenant->id,
                    $this->testBranch->id,
                    now()->month,
                    now()->year,
                    $formCode,
                    'preview'
                );

                $executionTime = (int)((microtime(true) - $startTime) * 1000);

                if ($result['status'] === 'success') {
                    $results[$formCode] = [
                        'status' => 'pass',
                        'execution_time' => $executionTime,
                        'rows_count' => $result['result']['rows_count'] ?? 0
                    ];
                } else {
                    $failures[$formCode] = $result['error'] ?? 'Unknown error';
                    $results[$formCode] = ['status' => 'fail', 'error' => $failures[$formCode]];
                }
            } catch (\Exception $e) {
                $failures[$formCode] = $e->getMessage();
                $results[$formCode] = ['status' => 'fail', 'error' => $e->getMessage()];
            }
        }

        $this->diagnostics['preview_pipeline'] = [
            'status' => count($failures) === 0 ? 'pass' : 'fail',
            'weight' => 30,
            'forms_tested' => count($forms),
            'forms_passed' => count($forms) - count($failures),
            'results' => $results
        ];

        foreach ($failures as $formCode => $error) {
            $this->analyzePreviewFailure($formCode, $error);
        }
    }

    private function analyzePreviewFailure(string $formCode, string $error): void
    {
        $apiService = FormApiServiceFactory::make($formCode);
        $generator = FormGeneratorFactory::make($formCode);
        $viewPath = FormTemplateRegistry::resolve($formCode);
        $viewExists = View::exists($viewPath);

        $rootCause = 'Unknown';
        $affectedFiles = [];

        if (!$apiService && !$generator) {
            $rootCause = 'No API service or generator found';
            $affectedFiles = ['FormApiServiceFactory', 'FormGeneratorFactory'];
        } elseif (!$viewExists) {
            $rootCause = 'Blade template not found';
            $affectedFiles = ["resources/views/compliance/forms/" . strtolower($formCode) . ".blade.php"];
        } elseif (strpos($error, 'prepareData') !== false) {
            $rootCause = 'Generator prepareData method failed';
            $affectedFiles = ["app/Services/Compliance/FormGenerator/{$formCode}Generator.php"];
        } elseif (strpos($error, 'API') !== false) {
            $rootCause = 'API service data fetch failed';
            $affectedFiles = ["app/Services/Compliance/FormApis/{$formCode}ApiService.php"];
        }

        $this->rootCauses[] = [
            'component' => 'Preview Pipeline',
            'form_code' => $formCode,
            'status' => 'fail',
            'root_cause' => $rootCause,
            'error_message' => $error,
            'affected_files' => $affectedFiles,
            'recommended_fix' => $this->getRecommendedFix($formCode, $rootCause)
        ];
    }

    private function testGeneratorAnalysis(): void
    {
        $generatorPath = app_path('Services/Compliance/FormGenerator');
        $files = File::files($generatorPath);

        $results = [];
        $issues = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (in_array($filename, ['BaseFormGenerator.php', 'FormGeneratorFactory.php', 'BladeMappingEngine.php', 'FormDataAggregator.php', 'FormValidationService.php'])) {
                continue;
            }

            $content = File::get($file->getRealPath());
            $hasPrepareData = strpos($content, 'protected function prepareData') !== false || strpos($content, 'public function prepareData') !== false;
            $hasHeader = preg_match('/[\'"]header[\'"]/', $content) > 0;
            $hasRows = preg_match('/[\'"]rows[\'"]/', $content) > 0;
            $hasTotals = preg_match('/[\'"]totals[\'"]/', $content) > 0;
            $hasIsNil = preg_match('/[\'"]is_nil[\'"]/', $content) > 0;

            $status = $hasPrepareData && $hasHeader && $hasRows ? 'pass' : 'fail';
            $results[$filename] = [
                'status' => $status,
                'has_prepare_data' => $hasPrepareData,
                'has_header' => $hasHeader,
                'has_rows' => $hasRows,
                'has_totals' => $hasTotals,
                'has_is_nil' => $hasIsNil
            ];

            if ($status === 'fail') {
                $missing = [];
                if (!$hasPrepareData) $missing[] = 'prepareData()';
                if (!$hasHeader) $missing[] = 'header';
                if (!$hasRows) $missing[] = 'rows';
                $issues[$filename] = $missing;
            }
        }

        $this->diagnostics['generators'] = [
            'status' => count($issues) === 0 ? 'pass' : 'fail',
            'weight' => 15,
            'total_generators' => count($results),
            'valid_generators' => count($results) - count($issues),
            'results' => $results
        ];

        foreach ($issues as $filename => $missing) {
            $this->rootCauses[] = [
                'component' => 'Form Generators',
                'file' => $filename,
                'status' => 'fail',
                'root_cause' => 'Missing required structure',
                'missing_elements' => $missing,
                'affected_files' => ["app/Services/Compliance/FormGenerator/{$filename}"],
                'recommended_fix' => "Implement missing elements: " . implode(', ', $missing)
            ];
        }
    }

    private function testBladeTemplateAnalysis(): void
    {
        $results = [];
        $issues = [];

        foreach (FormTemplateRegistry::getAll() as $formCode => $viewPath) {
            $status = View::exists($viewPath) ? 'pass' : 'fail';
            $results[$formCode] = [
                'status' => $status,
                'view_path' => $viewPath,
                'exists' => $status === 'pass'
            ];

            if ($status === 'fail') {
                $issues[$formCode] = "Template not found at {$viewPath}";
            }
        }

        $this->diagnostics['blade_templates'] = [
            'status' => count($issues) === 0 ? 'pass' : 'fail',
            'weight' => 10,
            'total_templates' => count($results),
            'valid_templates' => count($results) - count($issues),
            'results' => array_slice($results, 0, 10)
        ];

        foreach ($issues as $formCode => $issue) {
            $this->rootCauses[] = [
                'component' => 'Blade Templates',
                'form_code' => $formCode,
                'status' => 'fail',
                'root_cause' => 'Template file not found',
                'issue' => $issue,
                'affected_files' => ["resources/views/compliance/forms/" . strtolower($formCode) . ".blade.php"],
                'recommended_fix' => "Create template file for {$formCode}"
            ];
        }
    }

    private function testApiServiceAnalysis(): void
    {
        $apiPath = app_path('Services/Compliance/FormApis');
        $files = File::files($apiPath);

        $results = [];
        $issues = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (in_array($filename, ['BaseFormApiService.php', 'FormApiServiceFactory.php', 'FormApiServices.php'])) {
                continue;
            }

            $content = File::get($file->getRealPath());

            $hasTenantFilter = strpos($content, 'tenant_id') !== false;
            $hasBranchFilter = strpos($content, 'branch_id') !== false;
            $hasFetchMethod = strpos($content, 'public function fetch') !== false;
            $hasDbQuery = strpos($content, 'DB::table') !== false || strpos($content, '->where') !== false;

            $status = $hasTenantFilter && $hasBranchFilter && $hasFetchMethod ? 'pass' : 'fail';
            $results[$filename] = [
                'status' => $status,
                'has_tenant_filter' => $hasTenantFilter,
                'has_branch_filter' => $hasBranchFilter,
                'has_fetch_method' => $hasFetchMethod,
                'has_db_query' => $hasDbQuery
            ];

            if ($status === 'fail') {
                $missing = [];
                if (!$hasTenantFilter) $missing[] = 'tenant_id filtering';
                if (!$hasBranchFilter) $missing[] = 'branch_id filtering';
                if (!$hasFetchMethod) $missing[] = 'fetch() method';
                $issues[$filename] = $missing;
            }
        }

        $this->diagnostics['api_services'] = [
            'status' => count($issues) === 0 ? 'pass' : 'fail',
            'weight' => 15,
            'total_services' => count($results),
            'valid_services' => count($results) - count($issues),
            'results' => $results
        ];

        foreach ($issues as $filename => $missing) {
            $this->rootCauses[] = [
                'component' => 'API Services',
                'file' => $filename,
                'status' => 'fail',
                'root_cause' => 'Missing tenant/branch isolation',
                'missing_elements' => $missing,
                'affected_files' => ["app/Services/Compliance/FormApis/{$filename}"],
                'recommended_fix' => "Implement: " . implode(', ', $missing)
            ];
        }
    }

    private function testDatabaseDatasets(): void
    {
        $requiredTables = [
            'tenants',
            'branches',
            'workforce_employee',
            'workforce_payroll_entry',
            'workforce_payroll_cycle',
            'workforce_attendance',
            'contractor_master',
            'contract_labour_deployment'
        ];
        $results = [];
        $failures = [];
        $warnings = [];

        foreach ($requiredTables as $table) {
            $exists = DB::connection()->getSchemaBuilder()->hasTable($table);
            $results[$table] = ['exists' => $exists];

            if ($exists) {
                $count = DB::table($table)->count();
                $results[$table]['record_count'] = $count;
                if ($count === 0) {
                    $warnings[$table] = 'Table exists but empty';
                }
            } else {
                $failures[$table] = 'Table does not exist';
            }
        }

        $status = count($failures) === 0 ? 'pass' : 'fail';
        $this->diagnostics['database_datasets'] = [
            'status' => $status,
            'weight' => 10,
            'tables_checked' => count($requiredTables),
            'tables_exist' => count($requiredTables) - count($failures),
            'tables_with_data' => count($requiredTables) - count($failures) - count($warnings),
            'results' => $results
        ];

        foreach ($failures as $table => $issue) {
            $this->rootCauses[] = [
                'component' => 'Database Datasets',
                'table' => $table,
                'status' => 'fail',
                'root_cause' => $issue,
                'affected_files' => ['database'],
                'recommended_fix' => "Create {$table} table via migration"
            ];
        }

        foreach ($warnings as $table => $issue) {
            $this->rootCauses[] = [
                'component' => 'Database Datasets',
                'table' => $table,
                'status' => 'warning',
                'root_cause' => $issue,
                'affected_files' => ['database'],
                'recommended_fix' => "Seed {$table} table with test data"
            ];
        }
    }

    private function testPdfGeneration(): void
    {
        $forms = ['FORM_B', 'FORM_XVI'];
        $results = [];
        $failures = [];

        foreach ($forms as $formCode) {
            try {
                $result = $this->orchestrator->execute(
                    $this->testTenant->id,
                    $this->testBranch->id,
                    now()->month,
                    now()->year,
                    $formCode,
                    'pdf'
                );

                if ($result['status'] === 'success') {
                    $size = $result['result']['size'] ?? 0;
                    $results[$formCode] = [
                        'status' => $size > 0 ? 'pass' : 'fail',
                        'size' => $size,
                        'mime_type' => $result['result']['mime_type'] ?? 'unknown'
                    ];
                    if ($size === 0) {
                        $failures[$formCode] = 'PDF generated but empty';
                    }
                } else {
                    $failures[$formCode] = $result['error'] ?? 'Unknown error';
                    $results[$formCode] = ['status' => 'fail', 'error' => $failures[$formCode]];
                }
            } catch (\Exception $e) {
                $failures[$formCode] = $e->getMessage();
                $results[$formCode] = ['status' => 'fail', 'error' => $e->getMessage()];
            }
        }

        $this->diagnostics['pdf_generation'] = [
            'status' => count($failures) === 0 ? 'pass' : 'fail',
            'weight' => 10,
            'forms_tested' => count($forms),
            'forms_passed' => count($forms) - count($failures),
            'results' => $results
        ];

        foreach ($failures as $formCode => $error) {
            $this->rootCauses[] = [
                'component' => 'PDF Generation',
                'form_code' => $formCode,
                'status' => 'fail',
                'root_cause' => 'PDF generation failed',
                'error_message' => $error,
                'affected_files' => ["app/Services/Compliance/FormGenerator/{$formCode}Generator.php"],
                'recommended_fix' => 'Check PDF generation configuration and form data structure'
            ];
        }
    }

    private function testInspectionPack(): void
    {
        try {
            $packDir = "compliance_inspection_packs/{$this->testTenant->id}";
            $fullPath = storage_path("app/{$packDir}");
            
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0755, true);
            }

            if (!is_dir($fullPath)) {
                throw new \Exception("Cannot create directory: {$fullPath}");
            }

            $zipFileName = "inspection_pack_test_" . time() . ".zip";
            $zipPath = $fullPath . DIRECTORY_SEPARATOR . $zipFileName;

            $zip = new \ZipArchive();
            $openResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            
            if ($openResult !== true) {
                throw new \Exception("Failed to create ZIP archive (error code: {$openResult})");
            }

            $zip->addFromString('test.txt', 'Test inspection pack');
            $zip->close();

            if (!file_exists($zipPath) || filesize($zipPath) === 0) {
                throw new \Exception('ZIP archive not created or empty');
            }

            $this->diagnostics['inspection_pack'] = [
                'status' => 'pass',
                'weight' => 5,
                'zip_created' => true,
                'zip_size' => filesize($zipPath),
                'file_count' => 1
            ];
        } catch (\Exception $e) {
            $this->diagnostics['inspection_pack'] = [
                'status' => 'fail',
                'weight' => 5,
                'error' => $e->getMessage()
            ];

            $this->rootCauses[] = [
                'component' => 'Inspection Pack',
                'status' => 'fail',
                'root_cause' => 'ZIP creation failed',
                'error_message' => $e->getMessage(),
                'affected_files' => ['app/Services/Compliance/InspectionPackService.php'],
                'recommended_fix' => 'Verify ZIP archive creation and storage permissions'
            ];
        }
    }

    private function testSecurityIsolation(): void
    {
        $issues = [];

        $orchestratorPath = app_path('Services/Compliance/ComplianceOrchestrator.php');
        $content = File::get($orchestratorPath);

        if (strpos($content, 'validateSubscriptionAccess') === false) {
            $issues[] = 'Missing subscription validation';
        }

        $apiPath = app_path('Services/Compliance/FormApis');
        $apiFiles = File::files($apiPath);
        $tenantIsolationIssues = 0;

        foreach ($apiFiles as $file) {
            if (in_array($file->getFilename(), ['BaseFormApiService.php', 'FormApiServiceFactory.php'])) {
                continue;
            }
            $apiContent = File::get($file->getRealPath());
            if (strpos($apiContent, 'tenant_id') === false) {
                $tenantIsolationIssues++;
            }
        }

        if ($tenantIsolationIssues > 0) {
            $issues[] = "Tenant isolation missing in {$tenantIsolationIssues} API services";
        }

        $branchIsolationIssues = 0;
        foreach ($apiFiles as $file) {
            if (in_array($file->getFilename(), ['BaseFormApiService.php', 'FormApiServiceFactory.php'])) {
                continue;
            }
            $apiContent = File::get($file->getRealPath());
            if (strpos($apiContent, 'branch_id') === false) {
                $branchIsolationIssues++;
            }
        }

        if ($branchIsolationIssues > 0) {
            $issues[] = "Branch isolation missing in {$branchIsolationIssues} API services";
        }

        $this->diagnostics['security'] = [
            'status' => count($issues) === 0 ? 'pass' : 'fail',
            'weight' => 5,
            'checks' => ['subscription_gating', 'tenant_isolation', 'branch_isolation'],
            'issues' => $issues
        ];

        if (count($issues) > 0) {
            $this->rootCauses[] = [
                'component' => 'Security',
                'status' => 'fail',
                'root_cause' => 'Multi-tenant isolation not fully enforced',
                'issues' => $issues,
                'affected_files' => ['app/Services/Compliance/ComplianceOrchestrator.php', 'app/Services/Compliance/FormApis/'],
                'recommended_fix' => 'Implement subscription validation and tenant/branch filtering in all API services'
            ];
        }
    }

    private function calculateHealthScore(): int
    {
        $totalWeight = 0;
        $weightedScore = 0;

        foreach ($this->diagnostics as $component => $data) {
            $weight = $data['weight'] ?? 0;
            $status = $data['status'] ?? 'fail';

            $componentScore = $status === 'pass' ? 100 : 0;
            $weightedScore += $componentScore * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? (int)($weightedScore / $totalWeight) : 0;
    }

    private function buildReport(?int $healthScore = null): array
    {
        $executionTime = (int)((microtime(true) - $this->startTime) * 1000);

        return [
            'status' => $healthScore === 100 ? 'healthy' : ($healthScore >= 70 ? 'warning' : 'critical'),
            'health_score' => $healthScore ?? 0,
            'execution_time' => $executionTime,
            'timestamp' => now()->toIso8601String(),
            'diagnostics' => $this->diagnostics,
            'root_causes' => $this->rootCauses,
            'summary' => [
                'total_components_tested' => count($this->diagnostics),
                'components_passed' => count(array_filter($this->diagnostics, fn($d) => $d['status'] === 'pass')),
                'components_failed' => count(array_filter($this->diagnostics, fn($d) => $d['status'] === 'fail')),
                'total_issues' => count($this->rootCauses)
            ]
        ];
    }

    private function getRecommendedFix(string $formCode, string $rootCause): string
    {
        return match ($rootCause) {
            'No API service or generator found' => "Create {$formCode}ApiService and {$formCode}Generator classes",
            'Blade template not found' => "Create resources/views/compliance/forms/{$formCode}.blade.php",
            'Generator prepareData method failed' => "Implement prepareData() method in {$formCode}Generator",
            'API service data fetch failed' => "Debug fetch() method in {$formCode}ApiService",
            default => "Review {$formCode} implementation"
        };
    }
}
