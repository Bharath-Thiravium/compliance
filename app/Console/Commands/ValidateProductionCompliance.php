<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateProductionCompliance extends Command
{
    protected $signature = 'compliance:validate-production';
    protected $description = 'Validate production-grade compliance automation system';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  COMPLIANCE ENGINE PRODUCTION VALIDATION');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $allPassed = true;

        // Phase 1: Master Data
        $this->info('PHASE 1: MASTER DATA VALIDATION');
        $sections = DB::table('compliance_sections')->count();
        $forms = DB::table('compliance_forms_master')->count();
        
        if ($sections === 4 && $forms === 36) {
            $this->info("✓ Sections: {$sections}/4");
            $this->info("✓ Forms: {$forms}/36");
        } else {
            $this->error("✗ Master data incomplete: {$sections} sections, {$forms} forms");
            $allPassed = false;
        }

        // Check distribution
        $distribution = DB::table('compliance_sections')
            ->select('section_code', 'section_name')
            ->get()
            ->map(function($s) {
                return [
                    'code' => $s->section_code,
                    'name' => $s->section_name,
                    'forms' => DB::table('compliance_forms_master')->where('section_id', DB::table('compliance_sections')->where('section_code', $s->section_code)->value('id'))->count()
                ];
            });

        foreach ($distribution as $dist) {
            $expected = match($dist['code']) {
                'FACTORIES' => 13,
                'CLRA' => 13,
                'SHOPS' => 7,
                'SOCIAL_SECURITY' => 3,
                default => 0
            };
            
            if ($dist['forms'] === $expected) {
                $this->info("  ✓ {$dist['code']}: {$dist['forms']}/{$expected} forms");
            } else {
                $this->error("  ✗ {$dist['code']}: {$dist['forms']}/{$expected} forms");
                $allPassed = false;
            }
        }
        $this->newLine();

        // Phase 2: Form Generator Coverage
        $this->info('PHASE 2: FORM GENERATOR COVERAGE');
        $factory = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::class;
        $supportedForms = $factory::getSupportedForms();
        $this->info("✓ Generator supports {" . count($supportedForms) . "} forms");
        
        $allFormCodes = DB::table('compliance_forms_master')
            ->where('auto_generate', true)
            ->pluck('form_code')
            ->toArray();
        
        $unsupported = array_diff($allFormCodes, $supportedForms);
        if (empty($unsupported)) {
            $this->info("✓ All auto-generate forms have generators");
        } else {
            $this->error("✗ Missing generators for: " . implode(', ', $unsupported));
            $allPassed = false;
        }
        $this->newLine();

        // Phase 3: Subscription Enforcement
        $this->info('PHASE 3: SUBSCRIPTION ENFORCEMENT');
        $middleware = file_exists(app_path('Http/Middleware/EnforceFullSubscription.php'));
        if ($middleware) {
            $this->info("✓ EnforceFullSubscription middleware exists");
        } else {
            $this->error("✗ EnforceFullSubscription middleware missing");
            $allPassed = false;
        }
        $this->newLine();

        // Phase 4: Database Schema
        $this->info('PHASE 4: DATABASE SCHEMA VALIDATION');
        $requiredTables = [
            'compliance_sections',
            'compliance_forms_master',
            'compliance_execution_batches',
            'compliance_generation_logs',
            'compliance_audit_logs',
            'tenants',
            'branches',
            'workforce_employee',
            'workforce_attendance',
        ];

        foreach ($requiredTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->info("  ✓ {$table}");
            } else {
                $this->error("  ✗ {$table} missing");
                $allPassed = false;
            }
        }
        $this->newLine();

        // Phase 5: Test Data
        $this->info('PHASE 5: TEST DATA AVAILABILITY');
        $tenants = DB::table('tenants')->count();
        $users = DB::table('users')->count();
        $branches = DB::table('branches')->count();
        
        $this->info("  Tenants: {$tenants}");
        $this->info("  Users: {$users}");
        $this->info("  Branches: {$branches}");
        
        if ($tenants > 0 && $users > 0 && $branches > 0) {
            $this->info("✓ Test data available");
        } else {
            $this->warn("⚠ Limited test data - run SystemStabilizationSeeder");
        }
        $this->newLine();

        // Final Status
        $this->info('═══════════════════════════════════════════════════════');
        if ($allPassed) {
            $this->info('  ✓ SYSTEM STATUS: PRODUCTION READY');
        } else {
            $this->error('  ✗ SYSTEM STATUS: ISSUES DETECTED');
        }
        $this->info('═══════════════════════════════════════════════════════');

        return $allPassed ? 0 : 1;
    }
}
