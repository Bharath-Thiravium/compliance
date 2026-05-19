<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ValidateOrchestratorRefactoring extends Command
{
    protected $signature = 'compliance:validate-refactoring';
    protected $description = 'Validate that all compliance workflows execute through ComplianceOrchestrator';

    public function handle()
    {
        $this->info('Starting Compliance Orchestrator Refactoring Validation...');
        $this->newLine();

        $issues = [];

        // 1. Check for duplicate FormDataAggregator
        $this->info('1. Checking for duplicate FormDataAggregator...');
        if (File::exists(app_path('Services/Compliance/FormGenerator/FormDataAggregator.php'))) {
            $issues[] = 'CRITICAL: Duplicate FormDataAggregator found at FormGenerator/FormDataAggregator.php';
            $this->error('   ❌ Duplicate FormDataAggregator exists');
        } else {
            $this->line('   ✅ No duplicate FormDataAggregator');
        }

        // 2. Check for direct generator calls in controllers
        $this->info('2. Checking for direct generator calls in controllers...');
        $controllerFiles = File::glob(app_path('Http/Controllers/**/*.php'));
        foreach ($controllerFiles as $file) {
            $content = File::get($file);
            if (preg_match('/FormGeneratorFactory::make|->make\(.*\)/', $content) && 
                !preg_match('/ComplianceOrchestrator/', $content)) {
                $issues[] = "Controller {$file} calls generator directly without orchestrator";
                $this->error("   ❌ {$file} has direct generator calls");
            }
        }
        if (empty($issues)) {
            $this->line('   ✅ No direct generator calls in controllers');
        }

        // 3. Check for direct aggregator calls in controllers
        $this->info('3. Checking for direct aggregator calls in controllers...');
        foreach ($controllerFiles as $file) {
            $content = File::get($file);
            if (preg_match('/FormDataAggregator|->aggregate\(/', $content) && 
                !preg_match('/ComplianceOrchestrator/', $content)) {
                $issues[] = "Controller {$file} calls aggregator directly without orchestrator";
                $this->error("   ❌ {$file} has direct aggregator calls");
            }
        }
        if (count($issues) <= 1) {
            $this->line('   ✅ No direct aggregator calls in controllers');
        }

        // 4. Check execution logs table
        $this->info('4. Checking compliance_execution_logs table...');
        if (!DB::getSchemaBuilder()->hasTable('compliance_execution_logs')) {
            $issues[] = 'CRITICAL: compliance_execution_logs table does not exist';
            $this->error('   ❌ Table does not exist');
        } else {
            $columns = DB::getSchemaBuilder()->getColumnListing('compliance_execution_logs');
            $required = ['tenant_id', 'branch_id', 'form_code', 'status', 'execution_time', 'records_generated'];
            $missing = array_diff($required, $columns);
            if (!empty($missing)) {
                $issues[] = "Missing columns in compliance_execution_logs: " . implode(', ', $missing);
                $this->error("   ❌ Missing columns: " . implode(', ', $missing));
            } else {
                $this->line('   ✅ Table has all required columns');
            }
        }

        // 5. Check for multi-tenant isolation in queries
        $this->info('5. Checking for multi-tenant isolation...');
        $serviceFiles = File::glob(app_path('Services/Compliance/**/*.php'));
        $tenantIssues = 0;
        foreach ($serviceFiles as $file) {
            $content = File::get($file);
            if (preg_match('/DB::table\(|->where\(/', $content)) {
                if (!preg_match('/tenant_id|branch_id/', $content)) {
                    $tenantIssues++;
                }
            }
        }
        if ($tenantIssues > 0) {
            $this->warn("   ⚠️  {$tenantIssues} files may have multi-tenant isolation issues");
        } else {
            $this->line('   ✅ Multi-tenant isolation appears correct');
        }

        // 6. Check ComplianceOrchestrator exists
        $this->info('6. Checking ComplianceOrchestrator...');
        if (!File::exists(app_path('Services/Compliance/ComplianceOrchestrator.php'))) {
            $issues[] = 'CRITICAL: ComplianceOrchestrator not found';
            $this->error('   ❌ ComplianceOrchestrator not found');
        } else {
            $this->line('   ✅ ComplianceOrchestrator exists');
        }

        // 7. Check API services exist
        $this->info('7. Checking API services...');
        if (!File::exists(app_path('Services/Compliance/FormApis'))) {
            $issues[] = 'WARNING: FormApis directory not found';
            $this->warn('   ⚠️  FormApis directory not found');
        } else {
            $apiFiles = File::glob(app_path('Services/Compliance/FormApis/*.php'));
            $this->line("   ✅ Found " . count($apiFiles) . " API services");
        }

        // 8. Check blade templates
        $this->info('8. Checking blade templates...');
        $bladeFiles = File::glob(resource_path('views/compliance/forms/*.blade.php'));
        if (empty($bladeFiles)) {
            $this->warn('   ⚠️  No blade templates found');
        } else {
            $this->line("   ✅ Found " . count($bladeFiles) . " blade templates");
        }

        // Summary
        $this->newLine();
        $this->info('=== VALIDATION SUMMARY ===');
        
        if (empty($issues)) {
            $this->info('✅ All validation checks passed!');
            $this->info('The project is ready for orchestrator-based execution.');
            return 0;
        } else {
            $this->error('❌ Found ' . count($issues) . ' issue(s):');
            foreach ($issues as $issue) {
                $this->error("   - {$issue}");
            }
            return 1;
        }
    }
}
