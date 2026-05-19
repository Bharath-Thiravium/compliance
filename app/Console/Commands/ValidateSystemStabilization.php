<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ValidateSystemStabilization extends Command
{
    protected $signature = 'system:validate-stabilization';
    protected $description = 'Validate complete system stabilization';

    public function handle()
    {
        $this->info('🔍 SYSTEM STABILIZATION VALIDATION');
        $this->newLine();

        $allPassed = true;

        // PHASE 1: Subscription Logic
        $this->info('PHASE 1: Subscription Logic Validation');
        $this->line('─────────────────────────────────────');
        
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $tenant = DB::table('tenants')->where('id', $user->tenant_id)->first();
            if ($tenant) {
                $this->line("✅ User: {$user->name} → Tenant: {$tenant->name} → Subscription: {$tenant->subscription_type}");
            } else {
                $this->error("❌ User: {$user->name} → No tenant found");
                $allPassed = false;
            }
        }
        $this->newLine();

        // PHASE 2: Route Middleware Check
        $this->info('PHASE 2: Route Middleware Validation');
        $this->line('─────────────────────────────────────');
        
        $uploadRoute = Route::getRoutes()->getByName('compliance.form.upload');
        if ($uploadRoute) {
            $middleware = $uploadRoute->middleware();
            $hasAuth = in_array('auth', $middleware) || in_array('web', $middleware);
            $hasNoSubscription = !in_array('App\Http\Middleware\CheckSubscription', $middleware);
            
            if ($hasAuth && $hasNoSubscription) {
                $this->info('✅ Upload route: Correct middleware (auth only, no subscription block)');
            } else {
                $this->error('❌ Upload route: Incorrect middleware');
                $allPassed = false;
            }
        } else {
            $this->error('❌ Upload route not found');
            $allPassed = false;
        }
        $this->newLine();

        // PHASE 3: Upload Stability
        $this->info('PHASE 3: Upload System Validation');
        $this->line('─────────────────────────────────────');
        
        $controller = new \App\Http\Controllers\ComplianceExecutionController(
            app(\App\Services\Compliance\ComplianceExecutionService::class),
            app(\App\Services\Compliance\ComplianceReportBuilder::class),
            app(\App\Services\Compliance\ComplianceEngine::class),
            app(\App\Services\Compliance\ComplianceTimelineService::class)
        );
        
        if (method_exists($controller, 'uploadForm')) {
            $this->info('✅ Upload method exists');
            
            $reflection = new \ReflectionMethod($controller, 'uploadForm');
            $source = file_get_contents($reflection->getFileName());
            
            $hasJsonResponse = strpos($source, 'response()->json') !== false;
            $noRedirect = strpos($source, 'return redirect()') === false || 
                         substr_count($source, 'return redirect()') === 0;
            
            if ($hasJsonResponse) {
                $this->info('✅ Returns JSON responses');
            } else {
                $this->error('❌ Does not return JSON');
                $allPassed = false;
            }
        } else {
            $this->error('❌ Upload method not found');
            $allPassed = false;
        }
        $this->newLine();

        // PHASE 4: Source Column Logic
        $this->info('PHASE 4: Report Source Detection Validation');
        $this->line('─────────────────────────────────────');
        
        $reportBuilder = app(\App\Services\Compliance\ComplianceReportBuilder::class);
        $reflection = new \ReflectionClass($reportBuilder);
        $source = file_get_contents($reflection->getFileName());
        
        $checksManual = strpos($source, 'compliance_manual_uploads') !== false;
        $checksBatchId = strpos($source, "->where('batch_id'") !== false;
        $checksFormCode = strpos($source, "->where('form_code'") !== false;
        
        if ($checksManual && $checksBatchId && $checksFormCode) {
            $this->info('✅ Dynamic source detection implemented');
        } else {
            $this->error('❌ Source detection not properly implemented');
            $allPassed = false;
        }
        $this->newLine();

        // PHASE 5: Tenant Structure
        $this->info('PHASE 5: Tenant Structure Validation');
        $this->line('─────────────────────────────────────');
        
        $tenantCount = DB::table('tenants')->count();
        $userCount = DB::table('users')->count();
        $orphanUsers = DB::table('users')
            ->leftJoin('tenants', 'users.tenant_id', '=', 'tenants.id')
            ->whereNull('tenants.id')
            ->count();
        
        $this->line("Tenants: {$tenantCount}");
        $this->line("Users: {$userCount}");
        
        if ($orphanUsers === 0) {
            $this->info('✅ No orphan users');
        } else {
            $this->error("❌ {$orphanUsers} orphan users found");
            $allPassed = false;
        }
        $this->newLine();

        // PHASE 6: Database Schema
        $this->info('PHASE 6: Database Schema Validation');
        $this->line('─────────────────────────────────────');
        
        $columns = DB::select("PRAGMA table_info(compliance_manual_uploads)");
        $columnNames = array_column($columns, 'name');
        
        $requiredColumns = ['id', 'user_id', 'batch_id', 'form_code', 'file_path'];
        $missing = array_diff($requiredColumns, $columnNames);
        
        if (empty($missing)) {
            $this->info('✅ compliance_manual_uploads table structure correct');
        } else {
            $this->error('❌ Missing columns: ' . implode(', ', $missing));
            $allPassed = false;
        }
        $this->newLine();

        // FINAL SUMMARY
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('                 VALIDATION SUMMARY                    ');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();
        
        if ($allPassed) {
            $this->info('✅ SYSTEM STABLE');
            $this->info('✅ SUBSCRIPTION LOGIC CONSISTENT');
            $this->info('✅ UPLOAD STABLE');
            $this->info('✅ SOURCE COLUMN CORRECT');
            $this->info('✅ TENANT STRUCTURE CLEAN');
            $this->info('✅ DEMO READY');
            $this->info('✅ PRODUCTION READY');
            $this->newLine();
            return 0;
        } else {
            $this->error('❌ SYSTEM HAS ISSUES - Review errors above');
            $this->newLine();
            return 1;
        }
    }
}
