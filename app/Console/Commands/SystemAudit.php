<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class SystemAudit extends Command
{
    protected $signature = 'system:audit';
    protected $description = 'Comprehensive system audit and stabilization';

    public function handle(): int
    {
        $this->info("═══════════════════════════════════════════════════════");
        $this->info("  COMPREHENSIVE SYSTEM AUDIT");
        $this->info("═══════════════════════════════════════════════════════");
        $this->newLine();

        $issues = [];

        // 1. Authentication Audit
        $this->info("[1/8] Authentication System Audit");
        $authIssues = $this->auditAuthentication();
        $issues = array_merge($issues, $authIssues);
        $this->newLine();

        // 2. Database Schema Audit
        $this->info("[2/8] Database Schema Audit");
        $schemaIssues = $this->auditDatabaseSchema();
        $issues = array_merge($issues, $schemaIssues);
        $this->newLine();

        // 3. Tenant Integrity Audit
        $this->info("[3/8] Tenant Integrity Audit");
        $tenantIssues = $this->auditTenantIntegrity();
        $issues = array_merge($issues, $tenantIssues);
        $this->newLine();

        // 4. Subscription Enforcement Audit
        $this->info("[4/8] Subscription Enforcement Audit");
        $subscriptionIssues = $this->auditSubscriptionEnforcement();
        $issues = array_merge($issues, $subscriptionIssues);
        $this->newLine();

        // 5. Route & Middleware Audit
        $this->info("[5/8] Route & Middleware Audit");
        $routeIssues = $this->auditRoutes();
        $issues = array_merge($issues, $routeIssues);
        $this->newLine();

        // 6. Session & CSRF Audit
        $this->info("[6/8] Session & CSRF Audit");
        $sessionIssues = $this->auditSession();
        $issues = array_merge($issues, $sessionIssues);
        $this->newLine();

        // 7. Data Integrity Audit
        $this->info("[7/8] Data Integrity Audit");
        $dataIssues = $this->auditDataIntegrity();
        $issues = array_merge($issues, $dataIssues);
        $this->newLine();

        // 8. System Configuration Audit
        $this->info("[8/8] System Configuration Audit");
        $configIssues = $this->auditConfiguration();
        $issues = array_merge($issues, $configIssues);
        $this->newLine();

        // Summary
        $this->info("═══════════════════════════════════════════════════════");
        if (empty($issues)) {
            $this->info("  ✅ SYSTEM STATUS: STABLE");
        } else {
            $this->error("  ❌ SYSTEM STATUS: " . count($issues) . " ISSUES FOUND");
            $this->newLine();
            $this->error("CRITICAL ISSUES:");
            foreach ($issues as $issue) {
                $this->error("  • " . $issue);
            }
        }
        $this->info("═══════════════════════════════════════════════════════");

        return empty($issues) ? 0 : 1;
    }

    private function auditAuthentication(): array
    {
        $issues = [];

        // Check users table exists and has correct columns
        if (!Schema::hasTable('users')) {
            $issues[] = "Users table missing";
            return $issues;
        }

        $userColumns = Schema::getColumnListing('users');
        $requiredColumns = ['id', 'name', 'email', 'password', 'tenant_id'];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $userColumns)) {
                $issues[] = "Users table missing column: {$column}";
            }
        }

        // Check if users exist
        $userCount = DB::table('users')->count();
        if ($userCount === 0) {
            $issues[] = "No users found in database";
        } else {
            $this->line("  ✅ Found {$userCount} users");
        }

        // Check password hashing
        $user = DB::table('users')->first();
        if ($user && !Hash::needsRehash($user->password)) {
            $this->line("  ✅ Password properly hashed");
        } elseif ($user) {
            $issues[] = "User passwords not properly hashed";
        }

        // Check sessions table
        if (!Schema::hasTable('sessions')) {
            $issues[] = "Sessions table missing (required for database sessions)";
        } else {
            $this->line("  ✅ Sessions table exists");
        }

        return $issues;
    }

    private function auditDatabaseSchema(): array
    {
        $issues = [];

        $requiredTables = [
            'users', 'tenants', 'branches', 'compliance_execution_batches',
            'compliance_generation_logs', 'compliance_forms_master',
            'compliance_sections', 'workforce_employee', 'workforce_payroll_entry'
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Missing table: {$table}";
            } else {
                $this->line("  ✅ Table exists: {$table}");
            }
        }

        // Check compliance_generation_logs schema
        if (Schema::hasTable('compliance_generation_logs')) {
            $columns = Schema::getColumnListing('compliance_generation_logs');
            $required = ['batch_id', 'tenant_id', 'form_code', 'status', 'generated_file_path'];
            
            foreach ($required as $column) {
                if (!in_array($column, $columns)) {
                    $issues[] = "compliance_generation_logs missing column: {$column}";
                }
            }
        }

        return $issues;
    }

    private function auditTenantIntegrity(): array
    {
        $issues = [];

        // Check tenants table
        if (!Schema::hasTable('tenants')) {
            $issues[] = "Tenants table missing";
            return $issues;
        }

        $tenantCount = DB::table('tenants')->count();
        if ($tenantCount === 0) {
            $issues[] = "No tenants found";
        } else {
            $this->line("  ✅ Found {$tenantCount} tenants");
        }

        // Check tenant columns
        $tenantColumns = Schema::getColumnListing('tenants');
        if (!in_array('subscription_type', $tenantColumns)) {
            $issues[] = "Tenants table missing subscription_type column";
        }

        // Check user-tenant relationships
        $usersWithoutTenant = DB::table('users')->whereNull('tenant_id')->count();
        if ($usersWithoutTenant > 0) {
            $issues[] = "{$usersWithoutTenant} users without tenant_id";
        } else {
            $this->line("  ✅ All users have tenant association");
        }

        // Check orphaned branches
        if (Schema::hasTable('branches')) {
            $orphanBranches = DB::table('branches as b')
                ->leftJoin('tenants as t', 'b.tenant_id', '=', 't.id')
                ->whereNull('t.id')
                ->count();
            
            if ($orphanBranches > 0) {
                $issues[] = "{$orphanBranches} branches with invalid tenant_id";
            }
        }

        return $issues;
    }

    private function auditSubscriptionEnforcement(): array
    {
        $issues = [];

        // Check if EnforceFullSubscription middleware exists
        if (!class_exists('App\\Http\\Middleware\\EnforceFullSubscription')) {
            $issues[] = "EnforceFullSubscription middleware missing";
        } else {
            $this->line("  ✅ EnforceFullSubscription middleware exists");
        }

        // Check subscription types in tenants
        if (Schema::hasTable('tenants')) {
            $subscriptions = DB::table('tenants')
                ->select('subscription_type')
                ->groupBy('subscription_type')
                ->pluck('subscription_type');
            
            $validTypes = ['MINIMAL', 'FULL'];
            foreach ($subscriptions as $type) {
                if (!in_array($type, $validTypes)) {
                    $issues[] = "Invalid subscription type found: {$type}";
                }
            }
            
            $this->line("  ✅ Subscription types: " . implode(', ', $subscriptions->toArray()));
        }

        return $issues;
    }

    private function auditRoutes(): array
    {
        $issues = [];

        // Check critical routes exist
        $routes = collect(\Route::getRoutes())->map(function ($route) {
            return $route->getName();
        })->filter();

        $requiredRoutes = [
            'login', 'logout', 'compliance.dashboard',
            'compliance.batch.create', 'compliance.batch.process'
        ];

        foreach ($requiredRoutes as $routeName) {
            if (!$routes->contains($routeName)) {
                $issues[] = "Missing route: {$routeName}";
            } else {
                $this->line("  ✅ Route exists: {$routeName}");
            }
        }

        return $issues;
    }

    private function auditSession(): array
    {
        $issues = [];

        $sessionDriver = config('session.driver');
        $envSessionDriver = env('SESSION_DRIVER');

        if ($sessionDriver !== $envSessionDriver) {
            $issues[] = "Session driver mismatch: config={$sessionDriver}, env={$envSessionDriver}";
        } else {
            $this->line("  ✅ Session driver: {$sessionDriver}");
        }

        if ($sessionDriver === 'database' && !Schema::hasTable('sessions')) {
            $issues[] = "Database session driver configured but sessions table missing";
        }

        return $issues;
    }

    private function auditDataIntegrity(): array
    {
        $issues = [];

        // Check for orphaned batches
        if (Schema::hasTable('compliance_execution_batches')) {
            $orphanBatches = DB::table('compliance_execution_batches as b')
                ->leftJoin('tenants as t', 'b.tenant_id', '=', 't.id')
                ->whereNull('t.id')
                ->count();
            
            if ($orphanBatches > 0) {
                $issues[] = "{$orphanBatches} batches with invalid tenant_id";
            }
        }

        // Check for orphaned generation logs
        if (Schema::hasTable('compliance_generation_logs')) {
            $orphanLogs = DB::table('compliance_generation_logs as g')
                ->leftJoin('tenants as t', 'g.tenant_id', '=', 't.id')
                ->whereNull('t.id')
                ->count();
            
            if ($orphanLogs > 0) {
                $issues[] = "{$orphanLogs} generation logs with invalid tenant_id";
            }
        }

        return $issues;
    }

    private function auditConfiguration(): array
    {
        $issues = [];

        // Check APP_KEY
        if (empty(config('app.key'))) {
            $issues[] = "APP_KEY not set";
        }

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->line("  ✅ Database connection working");
        } catch (\Exception $e) {
            $issues[] = "Database connection failed: " . $e->getMessage();
        }

        return $issues;
    }
}