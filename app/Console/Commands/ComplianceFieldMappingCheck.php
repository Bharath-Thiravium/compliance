<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\Registry\FormTemplateRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class ComplianceFieldMappingCheck extends Command
{
    protected $signature = 'compliance:field-map-check
                            {--tenant_id=1 : Tenant ID}
                            {--branch_id=1 : Branch ID}';

    protected $description = 'Validate field mappings across API, Generator, and Blade templates';

    public function handle(): int
    {
        $tenantId = (int) $this->option('tenant_id');
        $branchId = (int) $this->option('branch_id');

        $this->info('Checking field mappings for all compliance forms...');
        $this->newLine();

        $results = [];
        $forms = [
            'FORM_XII', 'FORM_XIII', 'FORM_XIV', 'FORM_XVI', 'FORM_XVII',
            'FORM_XIX', 'FORM_XX', 'FORM_XXI', 'FORM_XXII', 'FORM_XXIII',
            'FORM_A', 'FORM_C', 'FORM_D', 'FORM_D_ER',
            'FORM_11', 'ESI_FORM_12', 'EPF_INSPECTION',
            'FORM_B', 'FORM_2', 'FORM_10', 'FORM_12', 'FORM_17',
            'FORM_18', 'FORM_25', 'FORM_8', 'FORM_26', 'FORM_26A',
            'HAZARD_REG', 'SHOPS_FORM_C', 'SHOPS_UNPAID', 'SHOPS_FORM_12',
            'SHOPS_FORM_13', 'SHOPS_FINES', 'SHOPS_FORM_VI',
        ];

        foreach ($forms as $formCode) {
            $result = $this->checkForm($formCode, $tenantId, $branchId);
            $results[$formCode] = $result;
        }

        $this->displayResults($results);
        $this->saveReport($results);

        return 0;
    }

    private function checkForm(string $formCode, int $tenantId, int $branchId): array
    {
        $apiFields = [];
        $generatorFields = [];
        $templateFields = [];
        $errors = [];

        // Get API fields
        try {
            $api = FormApiServiceFactory::make($formCode);
            if ($api) {
                $data = $api->fetch($tenantId, $branchId, 1, 2024);
                $apiFields = array_keys($data['rows'][0] ?? []);
            }
        } catch (\Exception $e) {
            $errors[] = "API Error: " . $e->getMessage();
        }

        // Get Generator fields
        try {
            $generator = FormGeneratorFactory::make($formCode);
            if ($generator && method_exists($generator, 'prepareData')) {
                $dummyData = ['rows' => [array_fill_keys($apiFields, null)]];
                $formData = $generator->debugPrepareData($dummyData);
                $generatorFields = array_keys($formData['rows'][0] ?? []);
            }
        } catch (\Exception $e) {
            $errors[] = "Generator Error: " . $e->getMessage();
        }

        // Get Template fields
        try {
            $templatePath = FormTemplateRegistry::resolve($formCode);
            if ($templatePath && View::exists($templatePath)) {
                $templateFields = $this->extractTemplateFields($templatePath);
            }
        } catch (\Exception $e) {
            $errors[] = "Template Error: " . $e->getMessage();
        }

        // Validate mappings
        $missingInGenerator = array_diff($apiFields, $generatorFields);
        $missingInTemplate = array_diff($generatorFields, $templateFields);

        return [
            'api_fields' => $apiFields,
            'generator_fields' => $generatorFields,
            'template_fields' => $templateFields,
            'missing_in_generator' => $missingInGenerator,
            'missing_in_template' => $missingInTemplate,
            'errors' => $errors,
            'status' => empty($missingInGenerator) && empty($missingInTemplate) && empty($errors) ? 'OK' : 'WARNING',
        ];
    }

    private function extractTemplateFields(string $templatePath): array
    {
        $path = resource_path('views/' . str_replace('.', '/', $templatePath) . '.blade.php');
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $fields = [];

        // Extract $row->field and $row['field']
        preg_match_all('/\$row\s*->\s*(\w+)/', $content, $matches);
        $fields = array_merge($fields, $matches[1] ?? []);

        preg_match_all('/\$row\s*\[\s*[\'"](\w+)[\'"]\s*\]/', $content, $matches);
        $fields = array_merge($fields, $matches[1] ?? []);

        return array_unique($fields);
    }

    private function displayResults(array $results): void
    {
        $table = [];
        $okCount = 0;
        $warningCount = 0;
        $errorCount = 0;

        foreach ($results as $formCode => $result) {
            $status = $result['status'];
            $statusIcon = $status === 'OK' ? '✔' : '⚠';

            if ($status === 'OK') {
                $okCount++;
            } else {
                $warningCount++;
            }

            if (!empty($result['errors'])) {
                $errorCount++;
            }

            $issues = [];
            if (!empty($result['missing_in_generator'])) {
                $issues[] = "Missing in Generator: " . implode(', ', $result['missing_in_generator']);
            }
            if (!empty($result['missing_in_template'])) {
                $issues[] = "Missing in Template: " . implode(', ', $result['missing_in_template']);
            }
            if (!empty($result['errors'])) {
                $issues[] = implode('; ', $result['errors']);
            }

            $table[] = [
                $formCode,
                count($result['api_fields']),
                count($result['generator_fields']),
                count($result['template_fields']),
                $statusIcon,
                implode(' | ', $issues) ?: 'OK',
            ];
        }

        $this->table(
            ['Form', 'API Fields', 'Generator Fields', 'Template Fields', 'Status', 'Issues'],
            $table
        );

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Total Forms: " . count($results));
        $this->line("  ✔ OK: {$okCount}");
        $this->line("  ⚠ Warnings: {$warningCount}");
        $this->line("  ❌ Errors: {$errorCount}");
    }

    private function saveReport(array $results): void
    {
        $report = "Field Mapping Report - " . now() . "\n";
        $report .= str_repeat("=", 80) . "\n\n";

        foreach ($results as $formCode => $result) {
            $report .= "Form: {$formCode}\n";
            $report .= "Status: {$result['status']}\n";
            $report .= "API Fields: " . implode(', ', $result['api_fields']) . "\n";
            $report .= "Generator Fields: " . implode(', ', $result['generator_fields']) . "\n";
            $report .= "Template Fields: " . implode(', ', $result['template_fields']) . "\n";

            if (!empty($result['missing_in_generator'])) {
                $report .= "Missing in Generator: " . implode(', ', $result['missing_in_generator']) . "\n";
            }
            if (!empty($result['missing_in_template'])) {
                $report .= "Missing in Template: " . implode(', ', $result['missing_in_template']) . "\n";
            }
            if (!empty($result['errors'])) {
                $report .= "Errors: " . implode('; ', $result['errors']) . "\n";
            }

            $report .= "\n";
        }

        Storage::disk('local')->put('logs/compliance_field_mapping_report.log', $report);
        $this->info("Report saved to: storage/logs/compliance_field_mapping_report.log");
    }
}
