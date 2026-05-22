<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\WorkforceEmployee;
use App\Models\WorkforceAttendance;
use App\Models\PayrollEntry;
use App\Models\Contractor;
use App\Models\ContractLabourDeployment;
use App\Models\IncidentDocument;
use App\Models\HazardRegister;
use App\Models\EmployeeFinancialRegister;
use App\Models\BonusRecord;
use App\Models\EmployeeLeave;
use App\Models\Holiday;

class GenerateDemoDataset extends Command
{
    protected $signature = 'compliance:generate-demo-dataset';
    protected $description = 'Generate complete demo dataset for compliance forms';

    public function handle(): int
    {
        $this->info('🚀 Starting Demo Dataset Generation...');
        $this->newLine();

        try {
            $this->truncateTables();
            $this->seedData();
            $this->verifyData();
            $this->logSummary();

            $this->info('✅ Demo dataset generation completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function truncateTables(): void
    {
        $this->info('🗑️  Truncating demo tables...');

        $tables = [
            'employee_leave',
            'holidays',
            'hazard_register',
            'employee_financial_register',
            'bonus_records',
            'incident_documents',
            'contract_labour_deployment',
            'contractors',
            'payroll_entries',
            'workforce_attendance',
            'workforce_employee',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("  ✓ Truncated $table");
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function seedData(): void
    {
        $this->info('🌱 Seeding demo data...');
        $this->call('db:seed', ['--class' => 'ComplianceDemoDatasetSeeder']);
    }

    private function verifyData(): void
    {
        $this->info('✔️  Verifying data counts...');
        $this->newLine();

        $counts = [
            'Employees' => WorkforceEmployee::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Attendance Records' => WorkforceAttendance::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Payroll Entries' => PayrollEntry::whereHas('payrollCycle', function ($q) {
                $q->where('tenant_id', 1)->where('branch_id', 1);
            })->count(),
            'Contractors' => Contractor::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Contract Labour Deployments' => ContractLabourDeployment::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Incidents' => IncidentDocument::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Hazard Register Entries' => HazardRegister::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Financial Transactions' => EmployeeFinancialRegister::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Bonus Records' => BonusRecord::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Leave Records' => EmployeeLeave::where('tenant_id', 1)->where('branch_id', 1)->count(),
            'Holidays' => Holiday::where('tenant_id', 1)->where('branch_id', 1)->count(),
        ];

        $this->table(['Data Type', 'Count', 'Status'], array_map(function ($label, $count) {
            return [
                $label,
                $count,
                $count > 0 ? '✅' : '⚠️',
            ];
        }, array_keys($counts), $counts));
    }

    private function logSummary(): void
    {
        $this->newLine();
        $this->info('📊 Dataset Summary:');
        $this->line('  • Tenant ID: 1');
        $this->line('  • Branch ID: 1');
        $this->line('  • Employees: 50');
        $this->line('  • Attendance Records: ~1500');
        $this->line('  • Payroll Entries: 150 (50 employees × 3 months)');
        $this->line('  • Contractors: 10');
        $this->line('  • Contract Labour Deployments: 30');
        $this->line('  • Incidents: 10');
        $this->line('  • Hazard Register Entries: 5');
        $this->line('  • Financial Transactions: 20');
        $this->line('  • Bonus Records: 50');
        $this->line('  • Leave Records: 30');
        $this->line('  • Holidays: 10');
        $this->newLine();
        $this->info('💡 Test with: php artisan compliance:test-generation');
    }
}
