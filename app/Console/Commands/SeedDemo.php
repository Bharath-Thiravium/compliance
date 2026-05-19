<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedDemo extends Command
{
    protected $signature = 'compliance:seed-demo {--truncate : Truncate tables before seeding}';
    protected $description = 'Seed demo dataset with proper foreign key handling';

    public function handle(): int
    {
        $this->info('🌱 Seeding demo dataset...');
        $this->newLine();

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $this->info('✅ Foreign key checks disabled');

            // Truncate tables if requested
            if ($this->option('truncate')) {
                $this->info('🗑️  Truncating demo tables...');
                $this->truncateTables();
                $this->info('✅ Tables truncated');
            }

            // Run seeders
            $this->info('🌱 Running seeders...');
            $this->call('db:seed', ['--class' => 'ComplianceDemoSeeder']);

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('✅ Foreign key checks re-enabled');

            $this->newLine();
            $this->info('✅ Demo dataset seeded successfully!');

            return 0;
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->error('❌ Seeding failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function truncateTables(): void
    {
        $tables = [
            'compliance_execution_logs',
            'compliance_batch_forms',
            'compliance_execution_batches',
            'compliance_timelines',
            'contract_labour',
            'contractors',
            'incidents',
            'bonus_records',
            'payroll_entries',
            'payroll_cycles',
            'workforce_attendance',
            'workforce_employee',
            'branches',
            'tenants',
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("  ✅ Truncated $table");
            }
        }
    }
}
