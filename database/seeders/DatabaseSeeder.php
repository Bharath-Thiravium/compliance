<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bootstrap required data first (clears old data)
        $this->call([
            CleanBootstrapSeeder::class,
        ]);

        // Seed manual compliance master list (independent of demo data)
        $this->call([
            ManualComplianceMasterSeeder::class,
        ]);

        // Then run comprehensive January 2025 demo data
        $this->call([
            January2025ComprehensiveSeeder::class,
            FillMissingDataSeeder::class,
        ]);
    }
}
