<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerifyComplianceMappings extends Command
{
    protected $signature = 'compliance:verify-mappings';
    protected $description = 'Verify database mappings for statutory compliance forms';

    private array $auditResults = [];

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  DATABASE MAPPING VERIFICATION');
        $this->info('  Enterprise Compliance Engine');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $this->verifyClraForms();
        $this->verifyFactoriesForms();
        $this->generateReport();

        return 0;
    }

    private function verifyClraForms()
    {
        $this->info('🔍 Verifying CLRA Forms...');
        $this->newLine();

        $forms = [
            'FORM_XVI' => ['table' => 'contract_labour_deployment', 'fields' => ['wage_rate', 'deployment_start']],
            'FORM_XVII' => ['table' => 'contract_labour_deployment', 'fields' => ['wage_rate', 'deployment_start']],
            'FORM_XIX' => ['table' => 'contract_labour_deployment', 'fields' => ['employee_id', 'contractor_id']],
            'FORM_XXI' => ['table' => 'contract_labour_deployment', 'fields' => ['deployment_start']],
        ];

        foreach ($forms as $formCode => $config) {
            $this->verifyForm($formCode, $config);
        }
    }

    private function verifyFactoriesForms()
    {
        $this->info('🔍 Verifying Factories Act Forms...');
        $this->newLine();

        $forms = [
            'FORM_8' => ['table' => 'incident_documents', 'fields' => ['incident_type', 'incident_date', 'description']],
            'FORM_11' => ['table' => 'incident_documents', 'fields' => ['incident_type', 'incident_date', 'employee_id']],
            'FORM_12' => ['table' => 'workforce_employee', 'fields' => ['employee_code', 'name', 'designation']],
            'FORM_17' => ['table' => 'workforce_employee', 'fields' => ['employee_code', 'name', 'date_of_joining']],
            'FORM_2' => ['table' => 'workforce_attendance', 'fields' => ['employee_id', 'attendance_date', 'status']],
            'FORM_18' => ['table' => 'workforce_employee', 'fields' => ['employee_code', 'name', 'date_of_joining']],
        ];

        foreach ($forms as $formCode => $config) {
            $this->verifyForm($formCode, $config);
        }
    }

    private function verifyForm(string $formCode, array $config)
    {
        $table = $config['table'];
        $requiredFields = $config['fields'];

        // Check table exists
        if (!Schema::hasTable($table)) {
            $this->error("  ✗ {$formCode}: Table '{$table}' NOT FOUND");
            $this->auditResults[$formCode] = ['status' => 'FAILED', 'reason' => 'Table missing'];
            return;
        }

        // Check tenant_id column
        if (!Schema::hasColumn($table, 'tenant_id')) {
            $this->warn("  ⚠ {$formCode}: Missing 'tenant_id' in '{$table}' (tenant isolation risk)");
        }

        // Check required fields
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!Schema::hasColumn($table, $field)) {
                $missingFields[] = $field;
            }
        }

        if (empty($missingFields)) {
            $this->info("  ✓ {$formCode}: All mappings verified");
            $this->auditResults[$formCode] = ['status' => 'PASSED', 'table' => $table];
        } else {
            $this->error("  ✗ {$formCode}: Missing fields: " . implode(', ', $missingFields));
            $this->auditResults[$formCode] = ['status' => 'FAILED', 'missing_fields' => $missingFields];
        }
    }

    private function generateReport()
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  VERIFICATION SUMMARY');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $passed = collect($this->auditResults)->where('status', 'PASSED')->count();
        $failed = collect($this->auditResults)->where('status', 'FAILED')->count();
        $total = count($this->auditResults);

        $this->info("Total Forms Verified: {$total}");
        $this->info("Passed: {$passed}");
        
        if ($failed > 0) {
            $this->error("Failed: {$failed}");
            $this->newLine();
            $this->error('⚠ ACTION REQUIRED: Some mappings are missing or incomplete');
        } else {
            $this->info("Failed: {$failed}");
            $this->newLine();
            $this->info('✓ ALL MAPPINGS VERIFIED - PRODUCTION READY');
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
    }
}
