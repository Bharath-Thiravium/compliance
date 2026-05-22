<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeDemoData extends Command
{
    protected $signature = 'demo:purge
                            {--tenant_id=1 : Tenant ID to target}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Delete attendance, payroll and employee records for a tenant — keeps tenant and user rows intact';

    public function handle(): int
    {
        $tenantId = (int) $this->option('tenant_id');

        $this->warn("Target  →  tenant_id={$tenantId}");
        $this->warn('Tenant row, branch row and user accounts will NOT be touched.');

        if (! $this->option('force') && ! $this->confirm('Delete attendance, payroll and employee data for this tenant?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($tenantId) {
            // 1. Attendance  (FK → workforce_employee)
            $this->deleteAndReport('workforce_attendance', 'tenant_id', $tenantId);

            // 2. Payroll entries  (FK → workforce_employee + workforce_payroll_cycle)
            $this->deleteAndReport('workforce_payroll_entry', 'tenant_id', $tenantId);

            // 3. Payroll cycles
            $this->deleteAndReport('workforce_payroll_cycle', 'tenant_id', $tenantId);

            // 4. Employees  (parent — deleted last)
            $this->deleteAndReport('workforce_employee', 'tenant_id', $tenantId);
        });

        $this->info('✓ Done. Tenant, branch and user records are untouched.');
        return self::SUCCESS;
    }

    private function deleteAndReport(string $table, string $column, int $value): void
    {
        $deleted = DB::table($table)->where($column, $value)->delete();
        $this->line("  {$table}: {$deleted} rows deleted");
    }
}
