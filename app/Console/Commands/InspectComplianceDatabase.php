<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectComplianceDatabase extends Command
{
    protected $signature = 'compliance:inspect-db {--tenant=1 : Tenant ID} {--branch=1 : Branch ID} {--month=1 : Month} {--year=2024 : Year}';
    protected $description = 'Inspect database for compliance data';

    public function handle()
    {
        $tenantId = (int)$this->option('tenant');
        $branchId = (int)$this->option('branch');
        $month = (int)$this->option('month');
        $year = (int)$this->option('year');

        $this->info("=== DATABASE INSPECTION ===");
        $this->info("Tenant: $tenantId, Branch: $branchId, Period: $month/$year\n");

        // Check critical tables
        $tables = [
            'workforce_employee' => ['tenant_id', 'branch_id'],
            'workforce_payroll_entries' => ['tenant_id', 'branch_id', 'period_month', 'period_year'],
            'workforce_attendance' => ['tenant_id', 'branch_id', 'attendance_date'],
            'workforce_incidents' => ['tenant_id', 'branch_id', 'incident_date'],
            'contractor_master' => ['tenant_id', 'branch_id'],
            'contract_labour_deployment' => ['tenant_id', 'branch_id'],
            'workforce_fines' => ['tenant_id', 'branch_id', 'fine_date'],
            'workforce_deductions' => ['tenant_id', 'branch_id'],
            'workforce_advances' => ['tenant_id', 'branch_id'],
        ];

        foreach ($tables as $table => $filterColumns) {
            $this->inspectTable($table, $tenantId, $branchId, $month, $year, $filterColumns);
        }

        // Check form registry
        $this->inspectFormRegistry();
    }

    private function inspectTable(
        string $table,
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        array $filterColumns
    ): void {
        if (!Schema::hasTable($table)) {
            $this->line("<error>✗ Table not found: $table</error>");
            return;
        }

        $query = DB::table($table);

        // Apply filters
        if (in_array('tenant_id', $filterColumns)) {
            $query->where('tenant_id', $tenantId);
        }
        if (in_array('branch_id', $filterColumns)) {
            $query->where('branch_id', $branchId);
        }
        if (in_array('period_month', $filterColumns)) {
            $query->where('period_month', $month);
        }
        if (in_array('period_year', $filterColumns)) {
            $query->where('period_year', $year);
        }

        $count = $query->count();
        $status = $count > 0 ? '<info>✓</info>' : '<error>✗</error>';
        $this->line("$status $table: $count records");

        if ($count > 0) {
            $sample = $query->limit(1)->first();
            if ($sample) {
                $this->line("   Sample: " . json_encode((array)$sample, JSON_UNESCAPED_SLASHES));
            }
        }
    }

    private function inspectFormRegistry(): void
    {
        $this->line("\n=== FORM REGISTRY ===");

        $forms = DB::table('compliance_forms_master')->get();
        $this->line("Total forms in registry: " . $forms->count());

        $formsByType = $forms->groupBy('form_type');
        foreach ($formsByType as $type => $typeForms) {
            $this->line("  $type: " . $typeForms->count() . " forms");
            foreach ($typeForms as $form) {
                $this->line("    - {$form->form_code}");
            }
        }
    }
}
