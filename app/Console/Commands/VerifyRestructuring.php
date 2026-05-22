<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyRestructuring extends Command
{
    protected $signature = 'compliance:verify-restructuring';
    protected $description = 'Verify user-based subscription restructuring';

    public function handle()
    {
        $this->info('🔍 RESTRUCTURING VERIFICATION');
        $this->newLine();

        $checks = [
            'User Subscription Column' => $this->checkUserSubscription(),
            'Manual Uploads Table' => $this->checkManualUploadsTable(),
            'Single Tenant Mode' => $this->checkSingleTenantMode(),
            'Subscription Modules Config' => $this->checkSubscriptionConfig(),
            'Active Tenant ID' => $this->checkActiveTenant(),
        ];

        $passed = 0;
        $failed = 0;

        foreach ($checks as $name => $result) {
            if ($result['status'] === 'PASS') {
                $this->info("✅ {$name}: PASS - {$result['message']}");
                $passed++;
            } else {
                $this->error("❌ {$name}: FAIL - {$result['message']}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Results: {$passed} passed, {$failed} failed");
        $this->newLine();

        if ($failed === 0) {
            $this->info('🎉 RESTRUCTURING VERIFIED - SYSTEM READY');
            return 0;
        } else {
            $this->error('⚠️  RESTRUCTURING INCOMPLETE');
            return 1;
        }
    }

    private function checkUserSubscription(): array
    {
        if (!Schema::hasColumn('users', 'subscription_type')) {
            return ['status' => 'FAIL', 'message' => 'subscription_type column missing'];
        }

        $count = DB::table('users')->whereNotNull('subscription_type')->count();
        return ['status' => 'PASS', 'message' => "{$count} users have subscription_type"];
    }

    private function checkManualUploadsTable(): array
    {
        if (!Schema::hasTable('compliance_manual_uploads')) {
            return ['status' => 'FAIL', 'message' => 'Table does not exist'];
        }

        return ['status' => 'PASS', 'message' => 'Table exists'];
    }

    private function checkSingleTenantMode(): array
    {
        $mode = config('app.single_tenant_mode');
        
        if (!$mode) {
            return ['status' => 'FAIL', 'message' => 'Single tenant mode not enabled'];
        }

        return ['status' => 'PASS', 'message' => 'Enabled'];
    }

    private function checkSubscriptionConfig(): array
    {
        $config = config('subscription_modules');
        
        if (!$config) {
            return ['status' => 'FAIL', 'message' => 'Configuration not found'];
        }

        if (!isset($config['MINIMAL']) || !isset($config['FULL'])) {
            return ['status' => 'FAIL', 'message' => 'MINIMAL or FULL config missing'];
        }

        $minimalForms = count($config['MINIMAL']['allowed_forms'] ?? []);
        return ['status' => 'PASS', 'message' => "MINIMAL: {$minimalForms} forms, FULL: all forms"];
    }

    private function checkActiveTenant(): array
    {
        $tenantId = config('app.active_tenant_id');
        
        if (!$tenantId) {
            return ['status' => 'FAIL', 'message' => 'Active tenant ID not set'];
        }

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        
        if (!$tenant) {
            return ['status' => 'FAIL', 'message' => "Tenant {$tenantId} not found"];
        }

        return ['status' => 'PASS', 'message' => "Tenant {$tenantId}: {$tenant->name}"];
    }
}
