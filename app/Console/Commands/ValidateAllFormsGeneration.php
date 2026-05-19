<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ComplianceFormsMaster;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use Carbon\Carbon;

class ValidateAllFormsGeneration extends Command
{
    protected $signature = 'compliance:validate-all-forms {--tenant_id=1} {--branch_id=1} {--month=1} {--year=2025}';
    protected $description = 'Validate that all 34 statutory forms can generate successfully';

    public function handle()
    {
        $tenantId = $this->option('tenant_id');
        $branchId = $this->option('branch_id');
        $month = $this->option('month');
        $year = $this->option('year');

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant {$tenantId} not found");
            return 1;
        }

        $branch = Branch::find($branchId);
        if (!$branch) {
            $this->error("Branch {$branchId} not found");
            return 1;
        }

        $this->info("Validating all forms for Tenant: {$tenant->name}, Branch: {$branch->branch_name}");
        $this->info("Period: {$month}/{$year}");
        $this->newLine();

        $forms = [
            // CLRA Forms (10)
            'FORM_XII', 'FORM_XIII', 'FORM_XIV', 'FORM_XVI', 'FORM_XVII',
            'FORM_XIX', 'FORM_XX', 'FORM_XXI', 'FORM_XXII', 'FORM_XXIII',
            // Labour Welfare Forms (4)
            'FORM_A', 'FORM_C', 'FORM_D', 'FORM_D_ER',
            // Social Security Forms (3)
            'FORM_11', 'ESI_FORM_12', 'EPF_INSPECTION',
            // Factories Act Forms (11)
            'FORM_B', 'FORM_2', 'FORM_8', 'FORM_10', 'FORM_12',
            'FORM_17', 'FORM_18', 'FORM_25', 'FORM_26', 'FORM_26A', 'HAZARD_REG',
            // Shops & Establishment Forms (6)
            'SHOPS_FORM_C', 'SHOPS_FORM_VI', 'SHOPS_FORM_12', 'SHOPS_FORM_13',
            'SHOPS_UNPAID', 'SHOPS_FINES',
        ];

        $factory = app(FormGeneratorFactory::class);
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($forms as $formCode) {
            try {
                $generator = $factory::make($formCode);

                if (!$generator) {
                    $results[$formCode] = ['status' => 'FAILED', 'reason' => 'No generator found'];
                    $failureCount++;
                    $this->line("❌ {$formCode}: No generator found");
                    continue;
                }

                $data = $generator->fetch($tenantId, $branchId, $month, $year);

                if (!$data) {
                    $results[$formCode] = ['status' => 'FAILED', 'reason' => 'No data returned'];
                    $failureCount++;
                    $this->line("❌ {$formCode}: No data returned");
                    continue;
                }

                // Verify data structure
                if (!isset($data['tenant_id']) || $data['tenant_id'] != $tenantId) {
                    $results[$formCode] = ['status' => 'FAILED', 'reason' => 'Tenant ID mismatch'];
                    $failureCount++;
                    $this->line("❌ {$formCode}: Tenant ID mismatch");
                    continue;
                }

                if (!isset($data['branch_id']) || $data['branch_id'] != $branchId) {
                    $results[$formCode] = ['status' => 'FAILED', 'reason' => 'Branch ID mismatch'];
                    $failureCount++;
                    $this->line("❌ {$formCode}: Branch ID mismatch");
                    continue;
                }

                $results[$formCode] = ['status' => 'SUCCESS', 'records' => $data['record_count'] ?? 0];
                $successCount++;
                $this->line("✅ {$formCode}: Generated successfully ({$data['record_count'] ?? 0} records)");
            } catch (\Exception $e) {
                $results[$formCode] = ['status' => 'ERROR', 'error' => $e->getMessage()];
                $failureCount++;
                $this->line("❌ {$formCode}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("=== VALIDATION SUMMARY ===");
        $this->info("Total Forms: " . count($forms));
        $this->info("✅ Success: {$successCount}");
        $this->info("❌ Failed: {$failureCount}");
        $this->info("Success Rate: " . round(($successCount / count($forms)) * 100, 2) . "%");

        if ($failureCount > 0) {
            $this->newLine();
            $this->warn("Failed Forms:");
            foreach ($results as $formCode => $result) {
                if ($result['status'] !== 'SUCCESS') {
                    $this->line("  - {$formCode}: {$result['reason'] ?? $result['error'] ?? 'Unknown error'}");
                }
            }
            return 1;
        }

        return 0;
    }
}
