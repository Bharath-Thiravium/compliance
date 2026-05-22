<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\PayrollProcessingService;

class ProcessPayroll extends Command
{
    protected $signature = 'compliance:process-payroll 
                            {tenant_id : Tenant ID}
                            {branch_id : Branch ID}
                            {month : Month (1-12)}
                            {year : Year}';

    protected $description = 'Process payroll from attendance data';

    public function handle(): int
    {
        $tenantId = (int) $this->argument('tenant_id');
        $branchId = (int) $this->argument('branch_id');
        $month = (int) $this->argument('month');
        $year = (int) $this->argument('year');

        $this->info("Processing payroll for Tenant {$tenantId}, Branch {$branchId}, {$month}/{$year}");
        $this->newLine();

        try {
            // Validate context
            \App\Services\Compliance\ComplianceContextValidator::validate($tenantId, $branchId, $month, $year);
            
            $service = new PayrollProcessingService();
            $summary = $service->processPayroll($tenantId, $branchId, $month, $year);

            $this->info('✅ Payroll processed successfully');
            $this->newLine();
            $this->line('Summary:');
            $this->line('  Employees Processed: ' . $summary['employees_processed']);
            $this->line('  Total Days Worked: ' . $summary['total_days_worked']);
            $this->line('  Total Gross Wages: ₹' . number_format($summary['total_gross_wages'], 2));
            $this->line('  Total Net Wages: ₹' . number_format($summary['total_net_wages'], 2));

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Payroll processing failed: ' . $e->getMessage());
            return 1;
        }
    }
}
