<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceDemoSeeder extends Seeder
{
    /**
     * Run the database seeders in correct dependency order
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            // Phase 1: Master Data
            $this->call([
                TenantSeeder::class,
                BranchSeeder::class,
            ]);

            // Phase 2: Employee & Contractor Masters
            $this->call([
                EmployeeSeeder::class,
                ContractorSeeder::class,
            ]);

            // Phase 3: Payroll Setup
            $this->call([
                PayrollCycleSeeder::class,
            ]);

            // Phase 4: Payroll Data
            $this->call([
                PayrollEntrySeeder::class,
                BonusSeeder::class,
            ]);

            // Phase 5: Attendance & Leave
            $this->call([
                AttendanceSeeder::class,
            ]);

            // Phase 6: Contract Labour
            $this->call([
                ContractLabourSeeder::class,
            ]);

            // Phase 7: Incidents
            $this->call([
                IncidentSeeder::class,
            ]);

            $this->command->info('✅ Demo dataset seeded successfully!');
        } catch (\Exception $e) {
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        } finally {
            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
}
