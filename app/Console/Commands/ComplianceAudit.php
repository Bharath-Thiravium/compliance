<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ComplianceAudit extends Command
{
    protected $signature = 'compliance:audit';
    protected $description = 'Audit compliance forms, database schema and demo datasets';

    public function handle()
    {
        $this->info("🔎 Starting Compliance Engine Audit...");
        $this->line("");

        $this->auditTables();
        $this->auditForms();
        $this->auditData();

        $this->line("");
        $this->info("✅ Compliance audit completed.");
    }

    private function auditTables()
    {
        $this->info("📊 Checking Required Tables...");

        $tables = [
            'workforce_employee',
            'workforce_payroll_entry',
            'workforce_payroll_cycle',
            'workforce_attendance',
            'workforce_fines',
            'workforce_deductions',
            'workforce_advances',
            'contract_labour_deployment',
            'contractor_master'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->line("✔ Table exists: $table");
            } else {
                $this->error("❌ Missing table: $table");
            }
        }

        $this->line("");
    }

    private function auditForms()
    {
        $this->info("📄 Checking Form Generator Mapping...");

        $forms = [
            'FORM_XII',
            'FORM_XIII',
            'FORM_XIV',
            'FORM_XVI',
            'FORM_XVII',
            'FORM_XX',
            'FORM_XXI',
            'FORM_XXII',
            'FORM_XXIII'
        ];

        foreach ($forms as $form) {

            $generator = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::make($form);

            if ($generator) {
                $this->line("✔ Generator exists: $form");
            } else {
                $this->error("❌ Missing generator: $form");
            }
        }

        $this->line("");
    }

    private function auditData()
    {
        $this->info("📦 Checking Demo Data Availability...");

        $datasets = [
            'Employees' => 'workforce_employee',
            'Payroll Entries' => 'workforce_payroll_entry',
            'Attendance' => 'workforce_attendance',
            'Fines' => 'workforce_fines',
            'Deductions' => 'workforce_deductions',
            'Advances' => 'workforce_advances',
            'Contract Labour' => 'contract_labour_deployment'
        ];

        foreach ($datasets as $name => $table) {

            if (!Schema::hasTable($table)) {
                $this->error("❌ Table missing for dataset: $name");
                continue;
            }

            $count = DB::table($table)->count();

            if ($count > 0) {
                $this->line("✔ $name dataset available ($count records)");
            } else {
                $this->warn("⚠ $name dataset empty");
            }
        }

        $this->line("");
    }
}
