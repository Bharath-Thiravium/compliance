<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\Registry\FormTemplateRegistry;

class CompliancePipelineCheck extends Command
{
    protected $signature = 'compliance:pipeline-check {--form= : Check specific form}';
    protected $description = 'Validate API → Generator → Template pipeline for all forms';

    public function handle()
    {
        $forms = $this->option('form') ? [$this->option('form')] : $this->getAllForms();
        
        $this->info("\n🔍 COMPLIANCE PIPELINE CHECK\n");
        $this->info(str_repeat('=', 80));

        $results = [];
        foreach ($forms as $formCode) {
            $results[$formCode] = $this->checkForm($formCode);
        }

        $this->displayResults($results);
        $this->displaySummary($results);
    }

    private function checkForm(string $formCode): array
    {
        $status = 'OK';
        $errors = [];

        // Check API Service
        try {
            $apiService = FormApiServiceFactory::make($formCode);
            if (!$apiService) {
                $status = 'FAIL';
                $errors[] = 'API service not found';
            }
        } catch (\Exception $e) {
            $status = 'FAIL';
            $errors[] = "API error: " . $e->getMessage();
        }

        // Check Generator
        try {
            $generator = FormGeneratorFactory::make($formCode);
            if (!$generator) {
                $status = 'FAIL';
                $errors[] = 'Generator not found';
            }
        } catch (\Exception $e) {
            $status = 'FAIL';
            $errors[] = "Generator error: " . $e->getMessage();
        }

        // Check Template
        try {
            $registry = new FormTemplateRegistry();
            if (!$registry->exists($formCode)) {
                $status = 'FAIL';
                $errors[] = 'Template not registered';
            }
        } catch (\Exception $e) {
            $status = 'FAIL';
            $errors[] = "Template error: " . $e->getMessage();
        }

        return [
            'status' => $status,
            'errors' => $errors,
        ];
    }

    private function displayResults(array $results): void
    {
        foreach ($results as $formCode => $result) {
            $icon = $result['status'] === 'OK' ? '✔' : '⚠';
            $this->line("{$icon} {$formCode:<20} {$result['status']}");
            
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->line("  └─ {$error}");
                }
            }
        }
    }

    private function displaySummary(array $results): void
    {
        $total = count($results);
        $ok = count(array_filter($results, fn($r) => $r['status'] === 'OK'));
        $failed = $total - $ok;

        $this->info("\n" . str_repeat('=', 80));
        $this->info("Total: {$total} | OK: {$ok} | Failed: {$failed}");
        
        if ($failed === 0) {
            $this->info("✅ All forms passed pipeline check!");
        } else {
            $this->error("❌ {$failed} forms failed validation");
        }
    }

    private function getAllForms(): array
    {
        return [
            'FORM_XII', 'FORM_XIII', 'FORM_XIV', 'FORM_XVI', 'FORM_XVII',
            'FORM_XIX', 'FORM_XX', 'FORM_XXI', 'FORM_XXII', 'FORM_XXIII',
            'FORM_A', 'FORM_C', 'FORM_D', 'FORM_D_ER',
            'FORM_11', 'ESI_FORM_12', 'EPF_INSPECTION',
            'FORM_B', 'FORM_2', 'FORM_8', 'FORM_10', 'FORM_12', 'FORM_17',
            'FORM_18', 'FORM_25', 'FORM_26', 'FORM_26A', 'HAZARD_REG',
            'SHOPS_FORM_C', 'SHOPS_FORM_12', 'SHOPS_FORM_13', 'SHOPS_FORM_VI',
            'SHOPS_UNPAID', 'SHOPS_FINES',
        ];
    }
}
