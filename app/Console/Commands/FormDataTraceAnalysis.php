<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Compliance\Registry\FormRegistry;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FormDataTraceAnalysis extends Command
{
    protected $signature = 'compliance:trace-form-data {--tenant_id=1} {--branch_id=1} {--month=} {--year=} {--form=}';
    protected $description = 'Trace form data pipeline to identify missing data issues';

    private array $traceReport = [];
    private int $tenantId;
    private int $branchId;
    private int $month;
    private int $year;

    public function handle()
    {
        $this->tenantId = (int)$this->option('tenant_id');
        $this->branchId = (int)$this->option('branch_id');
        $this->month = (int)($this->option('month') ?? now()->month);
        $this->year = (int)($this->option('year') ?? now()->year);

        $this->info('=== FORM DATA TRACE ANALYSIS ===');
        $this->info("Tenant: {$this->tenantId}, Branch: {$this->branchId}, Period: {$this->month}/{$this->year}");
        $this->newLine();

        // Validate tenant and branch
        if (!$this->validateTenantAndBranch()) {
            $this->error('Invalid tenant or branch');
            return 1;
        }

        // Get forms to trace
        $forms = $this->option('form') 
            ? [$this->option('form')] 
            : array_keys(FormRegistry::all());

        // Trace each form
        foreach ($forms as $formCode) {
            $this->traceForm($formCode);
        }

        // Generate report
        $this->generateReport();

        return 0;
    }

    private function traceForm(string $formCode): void
    {
        $this->line("Tracing: {$formCode}");
        
        $trace = [
            'form_code' => $formCode,
            'timestamp' => now()->toIso8601String(),
            'input_parameters' => [
                'tenant_id' => $this->tenantId,
                'branch_id' => $this->branchId,
                'month' => $this->month,
                'year' => $this->year,
            ],
            'stages' => []
        ];

        try {
            // Stage 1: API Service Detection
            $trace['stages']['api_service'] = $this->traceApiService($formCode);

            // Stage 2: Database Query Execution
            $trace['stages']['database_query'] = $this->traceDatabaseQuery($formCode);

            // Stage 3: Generator Execution
            $trace['stages']['generator'] = $this->traceGenerator($formCode);

            // Stage 4: Blade Template Verification
            $trace['stages']['blade_template'] = $this->traceBladeTemplate($formCode);

            // Determine status
            $trace['status'] = $this->determineStatus($trace);
            $trace['root_cause'] = $this->identifyRootCause($trace);

        } catch (\Exception $e) {
            $trace['status'] = 'ERROR';
            $trace['error'] = $e->getMessage();
            $trace['root_cause'] = 'Exception: ' . $e->getMessage();
        }

        $this->traceReport[$formCode] = $trace;
        $this->displayTraceResult($formCode, $trace);
    }

    private function traceApiService(string $formCode): array
    {
        $result = [
            'status' => 'NOT_FOUND',
            'service_class' => null,
            'has_service' => false,
        ];

        $apiService = FormApiServiceFactory::make($formCode);
        
        if ($apiService) {
            $result['has_service'] = true;
            $result['service_class'] = get_class($apiService);
            $result['status'] = 'FOUND';

            try {
                $data = $apiService->fetch($this->tenantId, $this->branchId, $this->month, $this->year);
                $result['records_fetched'] = $data['record_count'] ?? count($data['rows'] ?? []);
                $result['data_keys'] = array_keys($data);
            } catch (\Exception $e) {
                $result['status'] = 'FETCH_ERROR';
                $result['error'] = $e->getMessage();
                $result['records_fetched'] = 0;
            }
        } else {
            $result['status'] = 'FALLBACK_TO_AGGREGATOR';
        }

        return $result;
    }

    private function traceDatabaseQuery(string $formCode): array
    {
        $result = [
            'status' => 'PENDING',
            'dataset' => null,
            'records_found' => 0,
            'query_executed' => false,
            'sample_record' => null,
        ];

        try {
            // Determine dataset based on form type
            $dataset = $this->getDatasetForForm($formCode);
            $result['dataset'] = $dataset;

            if (!$dataset) {
                $result['status'] = 'UNKNOWN_DATASET';
                return $result;
            }

            // Execute query
            $query = $this->buildQueryForDataset($dataset);
            $records = $query->get();
            
            $result['records_found'] = $records->count();
            $result['query_executed'] = true;
            $result['status'] = $records->count() > 0 ? 'SUCCESS' : 'EMPTY_DATASET';

            if ($records->count() > 0) {
                $result['sample_record'] = $records->first()->toArray();
                $result['columns_available'] = array_keys($result['sample_record']);
            }

        } catch (\Exception $e) {
            $result['status'] = 'QUERY_ERROR';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function traceGenerator(string $formCode): array
    {
        $result = [
            'status' => 'NOT_FOUND',
            'generator_class' => null,
            'has_generator' => false,
            'output_rows' => 0,
            'output_structure' => null,
        ];

        $generator = FormGeneratorFactory::make($formCode);

        if (!$generator) {
            $result['status'] = 'NO_GENERATOR';
            return $result;
        }

        $result['has_generator'] = true;
        $result['generator_class'] = get_class($generator);
        $result['status'] = 'FOUND';

        try {
            // Get raw data
            $apiService = FormApiServiceFactory::make($formCode);
            $rawData = $apiService 
                ? $apiService->fetch($this->tenantId, $this->branchId, $this->month, $this->year)
                : [];

            // Call prepareData via reflection
            $reflection = new \ReflectionClass($generator);
            if ($reflection->hasMethod('prepareData')) {
                $method = $reflection->getMethod('prepareData');
                $method->setAccessible(true);
                $output = $method->invoke($generator, $rawData);

                $result['output_rows'] = count($output['rows'] ?? []);
                $result['output_structure'] = [
                    'has_header' => isset($output['header']),
                    'has_rows' => isset($output['rows']),
                    'has_totals' => isset($output['totals']),
                    'is_nil' => $output['is_nil'] ?? false,
                ];

                if ($result['output_rows'] > 0) {
                    $result['sample_output_row'] = $output['rows'][0];
                }

                $result['status'] = 'PREPARED';
            }
        } catch (\Exception $e) {
            $result['status'] = 'PREPARATION_ERROR';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function traceBladeTemplate(string $formCode): array
    {
        $result = [
            'status' => 'NOT_FOUND',
            'template_path' => null,
            'template_exists' => false,
            'expected_variables' => [],
        ];

        $templatePath = 'compliance.forms.' . strtolower($formCode);
        $result['template_path'] = $templatePath;

        if (view()->exists($templatePath)) {
            $result['template_exists'] = true;
            $result['status'] = 'FOUND';
            $result['expected_variables'] = [
                'form_title', 'form_code', 'header', 'rows', 'totals', 'is_nil'
            ];
        } else {
            $result['status'] = 'TEMPLATE_MISSING';
        }

        return $result;
    }

    private function determineStatus(array $trace): string
    {
        $stages = $trace['stages'];

        // Check for critical failures
        if ($stages['blade_template']['status'] === 'TEMPLATE_MISSING') {
            return 'FAIL_TEMPLATE_MISSING';
        }

        if ($stages['generator']['status'] === 'NO_GENERATOR') {
            return 'FAIL_NO_GENERATOR';
        }

        // Check for data flow issues
        if ($stages['database_query']['records_found'] === 0) {
            return 'WARNING_EMPTY_DATASET';
        }

        if ($stages['generator']['output_rows'] === 0 && $stages['database_query']['records_found'] > 0) {
            return 'WARNING_DATA_LOST_IN_GENERATOR';
        }

        if ($stages['generator']['output_rows'] > 0) {
            return 'PASS';
        }

        return 'UNKNOWN';
    }

    private function identifyRootCause(array $trace): string
    {
        $stages = $trace['stages'];

        if ($stages['blade_template']['status'] === 'TEMPLATE_MISSING') {
            return 'Blade template not found: ' . $stages['blade_template']['template_path'];
        }

        if ($stages['generator']['status'] === 'NO_GENERATOR') {
            return 'No generator registered for form';
        }

        if ($stages['generator']['status'] === 'PREPARATION_ERROR') {
            return 'Generator preparation error: ' . ($stages['generator']['error'] ?? 'Unknown');
        }

        if ($stages['database_query']['status'] === 'EMPTY_DATASET') {
            return 'No records in dataset: ' . $stages['database_query']['dataset'];
        }

        if ($stages['database_query']['status'] === 'QUERY_ERROR') {
            return 'Database query error: ' . ($stages['database_query']['error'] ?? 'Unknown');
        }

        if ($stages['api_service']['status'] === 'FETCH_ERROR') {
            return 'API service fetch error: ' . ($stages['api_service']['error'] ?? 'Unknown');
        }

        if ($stages['generator']['output_rows'] === 0 && $stages['database_query']['records_found'] > 0) {
            return 'Data lost during generator prepareData() - check field mapping';
        }

        return 'No issues detected';
    }

    private function getDatasetForForm(string $formCode): ?string
    {
        $datasetMap = [
            'FORM_B' => 'workforce_payroll_entry',
            'FORM_10' => 'workforce_payroll_entry',
            'FORM_25' => 'workforce_attendance',
            'FORM_12' => 'workforce_employee',
            'FORM_XVI' => 'contract_labour_deployment',
            'FORM_XVII' => 'workforce_payroll_entry',
            'FORM_XX' => 'workforce_deductions',
            'FORM_XXI' => 'workforce_fines',
            'FORM_XXII' => 'workforce_advances',
            'FORM_XXIII' => 'workforce_payroll_entry',
        ];

        return $datasetMap[$formCode] ?? null;
    }

    private function buildQueryForDataset(string $dataset)
    {
        return match ($dataset) {
            'workforce_payroll_entry' => DB::table('workforce_payroll_entry')
                ->where('tenant_id', $this->tenantId)
                ->where('branch_id', $this->branchId),
            'workforce_attendance' => DB::table('workforce_attendance')
                ->where('tenant_id', $this->tenantId),
            'workforce_employee' => DB::table('workforce_employee')
                ->where('tenant_id', $this->tenantId)
                ->where('branch_id', $this->branchId),
            'contract_labour_deployment' => DB::table('contract_labour_deployment')
                ->where('tenant_id', $this->tenantId),
            'workforce_deductions' => DB::table('workforce_deductions')
                ->where('tenant_id', $this->tenantId),
            'workforce_fines' => DB::table('workforce_fines')
                ->where('tenant_id', $this->tenantId),
            'workforce_advances' => DB::table('workforce_advances')
                ->where('tenant_id', $this->tenantId),
            default => null,
        };
    }

    private function displayTraceResult(string $formCode, array $trace): void
    {
        $status = $trace['status'];
        $statusColor = match ($status) {
            'PASS' => 'info',
            'WARNING_EMPTY_DATASET', 'WARNING_DATA_LOST_IN_GENERATOR' => 'comment',
            default => 'error',
        };

        $this->line("  Status: <{$statusColor}>{$status}</{$statusColor}>");
        $this->line("  Root Cause: {$trace['root_cause']}");
        $this->line("  DB Records: {$trace['stages']['database_query']['records_found']}");
        $this->line("  Generator Rows: {$trace['stages']['generator']['output_rows']}");
        $this->newLine();
    }

    private function generateReport(): void
    {
        $this->info('=== TRACE REPORT SUMMARY ===');
        $this->newLine();

        $reportPath = storage_path('logs/form_data_trace_report.log');
        $reportContent = $this->formatReport();

        file_put_contents($reportPath, $reportContent);

        $this->info("Report saved to: {$reportPath}");
        $this->newLine();

        // Display summary
        $passed = collect($this->traceReport)->where('status', 'PASS')->count();
        $warnings = collect($this->traceReport)->filter(fn($t) => str_starts_with($t['status'], 'WARNING'))->count();
        $failed = collect($this->traceReport)->filter(fn($t) => str_starts_with($t['status'], 'FAIL'))->count();

        $this->line("Summary:");
        $this->line("  <info>PASS: {$passed}</info>");
        $this->line("  <comment>WARNING: {$warnings}</comment>");
        $this->line("  <error>FAIL: {$failed}</error>");
    }

    private function formatReport(): string
    {
        $report = "FORM DATA TRACE ANALYSIS REPORT\n";
        $report .= "Generated: " . now()->toDateTimeString() . "\n";
        $report .= "Tenant: {$this->tenantId}, Branch: {$this->branchId}, Period: {$this->month}/{$this->year}\n";
        $report .= str_repeat("=", 80) . "\n\n";

        foreach ($this->traceReport as $formCode => $trace) {
            $report .= "FORM: {$formCode}\n";
            $report .= str_repeat("-", 80) . "\n";
            $report .= "Status: {$trace['status']}\n";
            $report .= "Root Cause: {$trace['root_cause']}\n";
            $report .= "API Service: {$trace['stages']['api_service']['service_class'] ?? 'NONE'}\n";
            $report .= "Dataset: {$trace['stages']['database_query']['dataset'] ?? 'UNKNOWN'}\n";
            $report .= "Records Found: {$trace['stages']['database_query']['records_found']}\n";
            $report .= "Generator Rows: {$trace['stages']['generator']['output_rows']}\n";
            $report .= "Blade Template: {$trace['stages']['blade_template']['template_path']}\n";
            $report .= "Template Exists: " . ($trace['stages']['blade_template']['template_exists'] ? 'YES' : 'NO') . "\n";
            $report .= "\n";
        }

        return $report;
    }

    private function validateTenantAndBranch(): bool
    {
        $tenant = DB::table('tenants')->where('id', $this->tenantId)->exists();
        if (!$tenant) {
            $this->error("Tenant {$this->tenantId} not found");
            return false;
        }

        $branch = DB::table('branches')
            ->where('id', $this->branchId)
            ->where('tenant_id', $this->tenantId)
            ->exists();

        if (!$branch) {
            $this->error("Branch {$this->branchId} not found for tenant {$this->tenantId}");
            return false;
        }

        return true;
    }
}
