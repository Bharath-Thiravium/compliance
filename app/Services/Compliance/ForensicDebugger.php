<?php

namespace App\Services\Compliance;

use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\Registry\FormTemplateRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

/**
 * ForensicDebugger - Trace pipeline execution step-by-step
 * 
 * STEP 1: API Service Output
 * STEP 2: Generator Output
 * STEP 3: Template Variables
 * STEP 4: Blade Rendering
 */
class ForensicDebugger
{
    private array $trace = [];

    public function traceForm(
        string $formCode,
        int $tenantId,
        int $branchId,
        int $month,
        int $year
    ): array {
        $this->trace = [
            'form_code' => $formCode,
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'timestamp' => now()->toIso8601String(),
            'steps' => []
        ];

        try {
            // STEP 1: API Service
            $this->traceApiService($formCode, $tenantId, $branchId, $month, $year);

            // STEP 2: Generator
            $this->traceGenerator($formCode);

            // STEP 3: Template
            $this->traceTemplate($formCode);

            // STEP 4: Full Pipeline
            $this->traceFullPipeline($formCode, $tenantId, $branchId, $month, $year);

        } catch (\Exception $e) {
            $this->trace['error'] = $e->getMessage();
            $this->trace['trace'] = $e->getTraceAsString();
        }

        return $this->trace;
    }

    private function traceApiService(
        string $formCode,
        int $tenantId,
        int $branchId,
        int $month,
        int $year
    ): void {
        $step = [
            'name' => 'API Service',
            'form_code' => $formCode,
            'status' => 'pending'
        ];

        try {
            $apiService = FormApiServiceFactory::make($formCode);
            
            if (!$apiService) {
                $step['status'] = 'not_found';
                $step['message'] = 'No API service registered';
                $this->trace['steps'][] = $step;
                return;
            }

            $step['service_class'] = get_class($apiService);
            $rawData = $apiService->fetch($tenantId, $branchId, $month, $year);

            $step['status'] = 'success';
            $step['record_count'] = count($rawData['records'] ?? []);
            $step['has_tenant'] = isset($rawData['tenant']);
            $step['has_branch'] = isset($rawData['branch']);
            $step['has_meta'] = isset($rawData['meta']);
            $step['meta'] = $rawData['meta'] ?? null;
            $step['tenant_keys'] = array_keys($rawData['tenant'] ?? []);
            $step['branch_keys'] = array_keys($rawData['branch'] ?? []);
            $step['first_record'] = $rawData['records'][0] ?? null;

            // Store for next step
            $this->trace['api_data'] = $rawData;

        } catch (\Exception $e) {
            $step['status'] = 'error';
            $step['error'] = $e->getMessage();
        }

        $this->trace['steps'][] = $step;
    }

    private function traceGenerator(string $formCode): void
    {
        $step = [
            'name' => 'Generator',
            'form_code' => $formCode,
            'status' => 'pending'
        ];

        try {
            $generator = FormGeneratorFactory::make($formCode);

            if (!$generator) {
                $step['status'] = 'not_found';
                $step['message'] = 'No generator registered';
                $this->trace['steps'][] = $step;
                return;
            }

            $step['generator_class'] = get_class($generator);

            if (!isset($this->trace['api_data'])) {
                $step['status'] = 'skipped';
                $step['message'] = 'No API data available';
                $this->trace['steps'][] = $step;
                return;
            }

            $formData = $generator->generate($this->trace['api_data']);

            $step['status'] = 'success';
            $step['has_header'] = isset($formData['header']);
            $step['has_rows'] = isset($formData['rows']);
            $step['has_totals'] = isset($formData['totals']);
            $step['row_count'] = count($formData['rows'] ?? []);
            $step['header_keys'] = array_keys($formData['header'] ?? []);
            $step['first_row'] = $formData['rows'][0] ?? null;
            $step['is_nil'] = $formData['is_nil'] ?? false;

            // Store for next step
            $this->trace['generator_data'] = $formData;

        } catch (\Exception $e) {
            $step['status'] = 'error';
            $step['error'] = $e->getMessage();
        }

        $this->trace['steps'][] = $step;
    }

    private function traceTemplate(string $formCode): void
    {
        $step = [
            'name' => 'Template',
            'form_code' => $formCode,
            'status' => 'pending'
        ];

        try {
            $viewPath = FormTemplateRegistry::resolve($formCode);
            $step['view_path'] = $viewPath;

            if (!View::exists($viewPath)) {
                $step['status'] = 'not_found';
                $step['message'] = 'Template not found';
                $this->trace['steps'][] = $step;
                return;
            }

            $step['status'] = 'exists';

            // Extract template variables by parsing the view file
            $viewFile = resource_path('views/' . str_replace('.', '/', $viewPath) . '.blade.php');
            if (file_exists($viewFile)) {
                $content = file_get_contents($viewFile);
                preg_match_all('/\$(\w+)/', $content, $matches);
                $step['referenced_variables'] = array_unique($matches[1]);
            }

        } catch (\Exception $e) {
            $step['status'] = 'error';
            $step['error'] = $e->getMessage();
        }

        $this->trace['steps'][] = $step;
    }

    private function traceFullPipeline(
        string $formCode,
        int $tenantId,
        int $branchId,
        int $month,
        int $year
    ): void {
        $step = [
            'name' => 'Full Pipeline',
            'form_code' => $formCode,
            'status' => 'pending'
        ];

        try {
            // Get API data
            $apiService = FormApiServiceFactory::make($formCode);
            if (!$apiService) {
                $step['status'] = 'failed';
                $step['error'] = 'No API service';
                $this->trace['steps'][] = $step;
                return;
            }

            $rawData = $apiService->fetch($tenantId, $branchId, $month, $year);

            // Get generator
            $generator = FormGeneratorFactory::make($formCode);
            if (!$generator) {
                $step['status'] = 'failed';
                $step['error'] = 'No generator';
                $this->trace['steps'][] = $step;
                return;
            }

            $formData = $generator->generate($rawData);

            // Get template
            $viewPath = FormTemplateRegistry::resolve($formCode);
            if (!View::exists($viewPath)) {
                $step['status'] = 'failed';
                $step['error'] = 'Template not found';
                $this->trace['steps'][] = $step;
                return;
            }

            // Prepare view data (same as orchestrator)
            $viewData = array_merge(
                $formData['header'] ?? [],
                [
                    'form_title' => $formData['header']['form_title'] ?? $formCode,
                    'form_code' => $formCode,
                    'period_month' => $month,
                    'period_year' => $year,
                    'header' => $formData['header'] ?? [],
                    'rows' => $formData['rows'] ?? [],
                    'entries' => $formData['rows'] ?? [],
                    'totals' => $formData['totals'] ?? [],
                    'is_nil' => $formData['is_nil'] ?? empty($formData['rows'])
                ]
            );

            // Try to render
            $html = View::make($viewPath, $viewData)->render();

            $step['status'] = 'success';
            $step['html_length'] = strlen($html);
            $step['html_preview'] = substr($html, 0, 500);
            $step['view_data_keys'] = array_keys($viewData);
            $step['missing_variables'] = $this->findMissingVariables($viewPath, $viewData);

        } catch (\Exception $e) {
            $step['status'] = 'error';
            $step['error'] = $e->getMessage();
            $step['trace'] = $e->getTraceAsString();
        }

        $this->trace['steps'][] = $step;
    }

    private function findMissingVariables(string $viewPath, array $viewData): array
    {
        $missing = [];
        
        try {
            $viewFile = resource_path('views/' . str_replace('.', '/', $viewPath) . '.blade.php');
            if (!file_exists($viewFile)) {
                return $missing;
            }

            $content = file_get_contents($viewFile);
            preg_match_all('/\$(\w+)/', $content, $matches);
            $referencedVars = array_unique($matches[1]);

            foreach ($referencedVars as $var) {
                if (!isset($viewData[$var])) {
                    $missing[] = $var;
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        return $missing;
    }

    public function getTrace(): array
    {
        return $this->trace;
    }

    public function printTrace(): string
    {
        $output = "\n=== FORENSIC DEBUG TRACE ===\n";
        $output .= "Form: {$this->trace['form_code']}\n";
        $output .= "Tenant: {$this->trace['tenant_id']}, Branch: {$this->trace['branch_id']}\n";
        $output .= "Period: {$this->trace['month']}/{$this->trace['year']}\n";
        $output .= "Timestamp: {$this->trace['timestamp']}\n\n";

        foreach ($this->trace['steps'] as $step) {
            $output .= "--- {$step['name']} ---\n";
            $output .= "Status: {$step['status']}\n";
            
            foreach ($step as $key => $value) {
                if ($key !== 'name' && $key !== 'status' && $key !== 'form_code') {
                    if (is_array($value)) {
                        $output .= "$key: " . json_encode($value) . "\n";
                    } else {
                        $output .= "$key: $value\n";
                    }
                }
            }
            $output .= "\n";
        }

        if (isset($this->trace['error'])) {
            $output .= "ERROR: {$this->trace['error']}\n";
        }

        return $output;
    }
}
