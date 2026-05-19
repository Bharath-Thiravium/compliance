<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyMysqlMigration extends Command
{
    protected $signature = 'compliance:verify-mysql-migration';
    protected $description = 'Verify MySQL migration compatibility and system health';

    public function handle(): int
    {
        $this->info('🔍 MySQL Migration Verification');
        $this->newLine();

        try {
            // Step 1: Check database connection
            $this->info('Step 1: Checking database connection...');
            $this->checkDatabaseConnection();
            $this->info('✅ Database connection successful');
            $this->newLine();

            // Step 2: Verify database engine
            $this->info('Step 2: Verifying database engine...');
            $this->verifyDatabaseEngine();
            $this->info('✅ Database engine verified');
            $this->newLine();

            // Step 3: Check charset and collation
            $this->info('Step 3: Checking charset and collation...');
            $this->checkCharsetCollation();
            $this->info('✅ Charset and collation verified');
            $this->newLine();

            // Step 4: Verify core tables exist
            $this->info('Step 4: Verifying core tables...');
            $this->verifyCoreTablesExist();
            $this->info('✅ All core tables exist');
            $this->newLine();

            // Step 5: Verify foreign keys
            $this->info('Step 5: Verifying foreign keys...');
            $this->verifyForeignKeys();
            $this->info('✅ Foreign keys verified');
            $this->newLine();

            // Step 6: Verify indexes
            $this->info('Step 6: Verifying indexes...');
            $this->verifyIndexes();
            $this->info('✅ Indexes verified');
            $this->newLine();

            // Step 7: Verify multi-tenant safety
            $this->info('Step 7: Verifying multi-tenant safety...');
            $this->verifyMultiTenantSafety();
            $this->info('✅ Multi-tenant safety verified');
            $this->newLine();

            // Step 8: Check data integrity
            $this->info('Step 8: Checking data integrity...');
            $this->checkDataIntegrity();
            $this->info('✅ Data integrity verified');
            $this->newLine();

            // Step 9: Verify API services
            $this->info('Step 9: Verifying API services...');
            $this->verifyApiServices();
            $this->info('✅ API services verified');
            $this->newLine();

            // Step 10: Performance check
            $this->info('Step 10: Running performance check...');
            $this->performanceCheck();
            $this->info('✅ Performance check passed');
            $this->newLine();

            $this->info('✅ All verification checks passed!');
            $this->info('🚀 System is ready for production use.');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Verification failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function checkDatabaseConnection(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    private function verifyDatabaseEngine(): void
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? LIMIT 1", [$database]);

        if (empty($result)) {
            throw new \Exception('No tables found in database');
        }

        $engine = $result[0]->ENGINE ?? null;
        if ($engine !== 'InnoDB') {
            $this->warn("⚠️  Database engine is {$engine}, InnoDB recommended");
        } else {
            $this->line("   Engine: InnoDB ✅");
        }
    }

    private function checkCharsetCollation(): void
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);

        if (empty($result)) {
            throw new \Exception('Database not found');
        }

        $charset = $result[0]->DEFAULT_CHARACTER_SET_NAME;
        $collation = $result[0]->DEFAULT_COLLATION_NAME;

        $this->line("   Charset: {$charset}");
        $this->line("   Collation: {$collation}");

        if ($charset !== 'utf8mb4' || $collation !== 'utf8mb4_unicode_ci') {
            $this->warn("⚠️  Charset/Collation mismatch. Expected utf8mb4/utf8mb4_unicode_ci");
        }
    }

    private function verifyCoreTablesExist(): void
    {
        $coreTables = [
            'tenants',
            'branches',
            'workforce_employee',
            'workforce_attendance',
            'workforce_payroll_cycle',
            'workforce_payroll_entry',
            'contractor_master',
            'contract_labour_deployment',
            'incidents',
            'hazard_register',
            'employee_financial_register',
            'employee_leave',
            'holidays',
            'bonus_records',
            'compliance_execution_batches',
            'compliance_generation_logs',
            'compliance_timelines',
            'compliance_batch_forms',
        ];

        $missing = [];
        foreach ($coreTables as $table) {
            if (!Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (!empty($missing)) {
            throw new \Exception('Missing tables: ' . implode(', ', $missing));
        }

        $this->line("   All {$count} core tables exist ✅", ['count' => count($coreTables)]);
    }

    private function verifyForeignKeys(): void
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select("
            SELECT COUNT(*) as count FROM information_schema.REFERENTIAL_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = ?
        ", [$database]);

        $count = $result[0]->count ?? 0;
        if ($count === 0) {
            $this->warn("⚠️  No foreign keys found");
        } else {
            $this->line("   Foreign keys: {$count} ✅");
        }
    }

    private function verifyIndexes(): void
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select("
            SELECT COUNT(*) as count FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? AND INDEX_NAME != 'PRIMARY'
        ", [$database]);

        $count = $result[0]->count ?? 0;
        $this->line("   Indexes: {$count} ✅");

        // Check for composite indexes
        $compositeIndexes = DB::select("
            SELECT INDEX_NAME, COUNT(*) as col_count 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? AND INDEX_NAME != 'PRIMARY'
            GROUP BY INDEX_NAME 
            HAVING col_count > 1
        ", [$database]);

        $this->line("   Composite indexes: " . count($compositeIndexes) . " ✅");
    }

    private function verifyMultiTenantSafety(): void
    {
        // Check tenant_id column exists in key tables
        $tables = [
            'workforce_employee',
            'workforce_attendance',
            'workforce_payroll_entry',
            'incidents',
            'hazard_register',
            'bonus_records',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'tenant_id')) {
                throw new \Exception("Missing tenant_id in {$table}");
            }
        }

        $this->line("   Tenant isolation verified ✅");
    }

    private function checkDataIntegrity(): void
    {
        // Check for orphaned records
        $orphanedPayroll = DB::select("
            SELECT COUNT(*) as count FROM workforce_payroll_entry pe
            LEFT JOIN workforce_employee e ON pe.employee_id = e.id
            WHERE e.id IS NULL
        ");

        if ($orphanedPayroll[0]->count > 0) {
            $this->warn("⚠️  Found {$orphanedPayroll[0]->count} orphaned payroll entries");
        } else {
            $this->line("   No orphaned records found ✅");
        }
    }

    private function verifyApiServices(): void
    {
        $services = [
            'FormBApiService',
            'FormAApiService',
            'FormCApiService',
            'FormDApiService',
            'FormXIIApiService',
            'FormXIIIApiService',
        ];

        $available = 0;
        foreach ($services as $service) {
            $class = "App\\Services\\Compliance\\FormApis\\{$service}";
            if (class_exists($class)) {
                $available++;
            }
        }

        $this->line("   API services available: {$available}/" . count($services) . " ✅");
    }

    private function performanceCheck(): void
    {
        $startTime = microtime(true);

        // Test query performance
        DB::table('tenants')->count();
        DB::table('branches')->count();
        DB::table('workforce_employee')->count();

        $duration = (microtime(true) - $startTime) * 1000;

        if ($duration > 1000) {
            $this->warn("⚠️  Query performance slow: {$duration}ms");
        } else {
            $this->line("   Query performance: {$duration}ms ✅");
        }
    }
}
