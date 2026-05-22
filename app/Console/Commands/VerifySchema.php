<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifySchema extends Command
{
    protected $signature = 'compliance:verify-schema';
    protected $description = 'Verify database schema integrity and foreign keys';

    public function handle(): int
    {
        $this->info('🔍 Verifying database schema...');
        $this->newLine();

        $errors = [];
        $warnings = [];

        // Check critical tables exist
        $criticalTables = [
            'tenants',
            'branches',
            'workforce_employee',
            'payroll_cycles',
            'payroll_entries',
            'workforce_attendance',
            'contractors',
            'contract_labour',
            'incidents',
            'compliance_execution_batches',
            'compliance_execution_logs',
        ];

        $this->info('Step 1: Checking critical tables...');
        foreach ($criticalTables as $table) {
            if (Schema::hasTable($table)) {
                $this->line("  ✅ $table");
            } else {
                $errors[] = "Missing table: $table";
                $this->error("  ❌ $table");
            }
        }
        $this->newLine();

        // Check foreign key relationships
        $this->info('Step 2: Checking foreign key relationships...');
        $fkChecks = [
            'branches' => ['tenant_id' => 'tenants'],
            'workforce_employee' => ['tenant_id' => 'tenants', 'branch_id' => 'branches'],
            'payroll_cycles' => ['tenant_id' => 'tenants'],
            'payroll_entries' => ['tenant_id' => 'tenants', 'employee_id' => 'workforce_employee', 'payroll_cycle_id' => 'payroll_cycles'],
            'bonus_records' => ['tenant_id' => 'tenants', 'employee_id' => 'workforce_employee'],
            'workforce_attendance' => ['tenant_id' => 'tenants', 'employee_id' => 'workforce_employee'],
            'contractors' => ['tenant_id' => 'tenants'],
            'contract_labour' => ['tenant_id' => 'tenants', 'contractor_id' => 'contractors', 'employee_id' => 'workforce_employee'],
            'incidents' => ['tenant_id' => 'tenants', 'branch_id' => 'branches'],
            'compliance_execution_logs' => ['tenant_id' => 'tenants', 'branch_id' => 'branches', 'batch_id' => 'compliance_execution_batches'],
        ];

        foreach ($fkChecks as $table => $fks) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($fks as $column => $refTable) {
                if (Schema::hasColumn($table, $column)) {
                    $this->line("  ✅ $table.$column → $refTable");
                } else {
                    $warnings[] = "Missing column: $table.$column";
                    $this->warn("  ⚠️  $table.$column (missing)");
                }
            }
        }
        $this->newLine();

        // Check indexes
        $this->info('Step 3: Checking indexes...');
        $indexChecks = [
            'tenants' => ['id'],
            'branches' => ['id', 'tenant_id'],
            'workforce_employee' => ['id', 'tenant_id', 'branch_id'],
            'payroll_cycles' => ['id', 'tenant_id'],
            'payroll_entries' => ['id', 'tenant_id', 'employee_id', 'payroll_cycle_id'],
            'workforce_attendance' => ['id', 'tenant_id', 'employee_id'],
            'contractors' => ['id', 'tenant_id'],
            'contract_labour' => ['id', 'tenant_id', 'contractor_id'],
            'incidents' => ['id', 'tenant_id', 'branch_id'],
            'compliance_execution_logs' => ['id', 'tenant_id', 'batch_id'],
        ];

        foreach ($indexChecks as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $this->line("  ✅ $table.$column");
                } else {
                    $warnings[] = "Missing index: $table.$column";
                    $this->warn("  ⚠️  $table.$column (missing)");
                }
            }
        }
        $this->newLine();

        // Check data integrity
        $this->info('Step 4: Checking data integrity...');
        
        $tenantCount = DB::table('tenants')->count();
        $this->line("  Tenants: $tenantCount");

        $branchCount = DB::table('branches')->count();
        $this->line("  Branches: $branchCount");

        $employeeCount = DB::table('workforce_employee')->count();
        $this->line("  Employees: $employeeCount");

        $payrollCycleCount = DB::table('payroll_cycles')->count();
        $this->line("  Payroll Cycles: $payrollCycleCount");

        $payrollEntryCount = DB::table('payroll_entries')->count();
        $this->line("  Payroll Entries: $payrollEntryCount");

        $attendanceCount = DB::table('workforce_attendance')->count();
        $this->line("  Attendance Records: $attendanceCount");

        $this->newLine();

        // Summary
        $this->info('Step 5: Summary');
        if (empty($errors)) {
            $this->info('✅ All critical checks passed!');
        } else {
            $this->error('❌ Critical errors found:');
            foreach ($errors as $error) {
                $this->error("  - $error");
            }
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Warnings:');
            foreach ($warnings as $warning) {
                $this->warn("  - $warning");
            }
        }

        $this->newLine();

        return empty($errors) ? 0 : 1;
    }
}
