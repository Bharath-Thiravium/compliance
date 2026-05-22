<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\FormApis\FormApiServiceFactory;

class VerifyComplianceSystem extends Command
{
    protected $signature = 'compliance:verify';
    protected $description = 'Verify compliance platform system integrity';

    public function handle()
    {
        $this->info('🔍 Starting Compliance Platform System Verification...\n');

        $results = [
            'api_services' => $this->verifyApiServices(),
            'generators' => $this->verifyGenerators(),
            'database_tables' => $this->verifyDatabaseTables(),
            'storage' => $this->verifyStorage(),
            'execution_logs' => $this->verifyExecutionLogs(),
        ];

        $this->displayResults($results);

        return $results['all_passed'] ? 0 : 1;
    }

    private function verifyApiServices(): array
    {
        $this->line('📡 Verifying API Services...');

        $forms = [
            'FORM_B', 'FORM_10', 'FORM_25', 'FORM_A', 'FORM_C', 'FORM_D',
            'FORM_XII', 'FORM_XIII', 'FORM_XVI', 'FORM_XVII', 'FORM_XIX',
            'FORM_XX', 'FORM_XXI', 'FORM_XXIII'
        ];

        $results = [];
        foreach ($forms as $form) {
            $service = FormApiServiceFactory::make($form);
            $results[$form] = $service !== null ? '✓' : '✗';
        }

        $passed = count(array_filter($results, fn($v) => $v === '✓'));
        $this->line("  Result: {$passed}/" . count($forms) . " API services registered\n");

        return [
            'passed' => $passed === count($forms),
            'details' => $results
        ];
    }

    private function verifyGenerators(): array
    {
        $this->line('🏭 Verifying Form Generators...');

        $forms = FormGeneratorFactory::getSupportedForms();
        $results = [];

        foreach ($forms as $form) {
            $generator = FormGeneratorFactory::make($form);
            $results[$form] = $generator !== null ? '✓' : '✗';
        }

        $passed = count(array_filter($results, fn($v) => $v === '✓'));
        $this->line("  Result: {$passed}/" . count($forms) . " generators available\n");

        return [
            'passed' => $passed === count($forms),
            'total_forms' => count($forms),
            'details' => $results
        ];
    }

    private function verifyDatabaseTables(): array
    {
        $this->line('🗄️  Verifying Database Tables...');

        $requiredTables = [
            'workforce_employee',
            'workforce_payroll_entry',
            'workforce_attendance',
            'workforce_fines',
            'workforce_advances',
            'contract_labour_deployment',
            'incident_documents',
            'bonus_records',
            'compliance_execution_logs',
            'compliance_execution_batches',
            'tenants',
            'branches'
        ];

        $results = [];
        foreach ($requiredTables as $table) {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            $results[$table] = $exists ? '✓' : '✗';

            if ($exists) {
                $count = DB::table($table)->count();
                $results[$table] .= " ({$count} records)";
            }
        }

        $passed = count(array_filter($results, fn($v) => str_starts_with($v, '✓')));
        $this->line("  Result: {$passed}/" . count($requiredTables) . " tables exist\n");

        return [
            'passed' => $passed === count($requiredTables),
            'details' => $results
        ];
    }

    private function verifyStorage(): array
    {
        $this->line('💾 Verifying Storage Configuration...');

        $directories = [
            'generated_forms',
            'temp',
            'compliance',
            'compliance_pdfs'
        ];

        $results = [];
        foreach ($directories as $dir) {
            try {
                Storage::disk('local')->makeDirectory($dir);
                $results[$dir] = '✓ (writable)';
            } catch (\Exception $e) {
                $results[$dir] = '✗ (not writable)';
            }
        }

        $passed = count(array_filter($results, fn($v) => str_contains($v, 'writable')));
        $this->line("  Result: {$passed}/" . count($directories) . " directories writable\n");

        return [
            'passed' => $passed === count($directories),
            'details' => $results
        ];
    }

    private function verifyExecutionLogs(): array
    {
        $this->line('📋 Verifying Execution Logging...');

        $tableExists = DB::getSchemaBuilder()->hasTable('compliance_execution_logs');
        $logCount = $tableExists ? DB::table('compliance_execution_logs')->count() : 0;

        $results = [
            'table_exists' => $tableExists ? '✓' : '✗',
            'log_records' => $logCount
        ];

        $this->line("  Result: Table exists: " . ($tableExists ? 'Yes' : 'No') . ", Records: {$logCount}\n");

        return [
            'passed' => $tableExists,
            'details' => $results
        ];
    }

    private function displayResults(array $results): void
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('COMPLIANCE PLATFORM VERIFICATION RESULTS');
        $this->info('═══════════════════════════════════════════════════════════\n');

        $allPassed = true;

        foreach ($results as $component => $result) {
            if ($component === 'all_passed') continue;

            $status = $result['passed'] ?? false ? '✅ PASS' : '❌ FAIL';
            $this->line("{$status} - " . ucfirst(str_replace('_', ' ', $component)));

            if (isset($result['details'])) {
                foreach ($result['details'] as $key => $value) {
                    $this->line("    • {$key}: {$value}");
                }
            }

            if (isset($result['total_forms'])) {
                $this->line("    • Total Forms: {$result['total_forms']}");
            }

            $this->line('');

            if (!($result['passed'] ?? false)) {
                $allPassed = false;
            }
        }

        $this->info('═══════════════════════════════════════════════════════════');

        if ($allPassed) {
            $this->info('✅ SYSTEM STATUS: PRODUCTION READY');
        } else {
            $this->error('❌ SYSTEM STATUS: ISSUES DETECTED');
        }

        $this->info('═══════════════════════════════════════════════════════════\n');
    }
}
