<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class ComplianceDebugCommand extends Command
{
    protected $signature = 'compliance:debug {--tenant=1} {--branch=1} {--month=} {--year=}';
    protected $description = 'Debug all compliance form services and generate health report';

    private array $formServices = [
        'FORM_2' => \App\Services\Compliance\Forms\Form2Service::class,
        'FORM_8' => \App\Services\Compliance\Forms\Form8Service::class,
        'FORM_10' => \App\Services\Compliance\Forms\Form10Service::class,
        'FORM_11' => \App\Services\Compliance\Forms\Form11Service::class,
        'FORM_12' => \App\Services\Compliance\Forms\Form12Service::class,
        'FORM_17' => \App\Services\Compliance\Forms\Form17Service::class,
        'FORM_18' => \App\Services\Compliance\Forms\Form18Service::class,
        'FORM_25' => \App\Services\Compliance\Forms\Form25Service::class,
        'FORM_26' => \App\Services\Compliance\Forms\Form26Service::class,
        'FORM_26A' => \App\Services\Compliance\Forms\Form26AService::class,
        'FORM_XII' => \App\Services\Compliance\Forms\FormXIIService::class,
        'FORM_XIII' => \App\Services\Compliance\Forms\FormXIIIService::class,
        'FORM_XIV' => \App\Services\Compliance\Forms\FormXIVService::class,
        'FORM_XVI' => \App\Services\Compliance\Forms\FormXVIService::class,
        'FORM_XVII' => \App\Services\Compliance\Forms\FormXVIIService::class,
        'FORM_XVIII' => \App\Services\Compliance\Forms\FormXVIIIService::class,
        'FORM_XIX' => \App\Services\Compliance\Forms\FormXIXService::class,
        'FORM_XX' => \App\Services\Compliance\Forms\FormXXService::class,
        'FORM_XXI' => \App\Services\Compliance\Forms\FormXXIService::class,
        'FORM_XXII' => \App\Services\Compliance\Forms\FormXXIIService::class,
        'FORM_XXIII' => \App\Services\Compliance\Forms\FormXXIIIService::class,
        'FORM_A' => \App\Services\Compliance\Forms\FormAService::class,
        'FORM_B' => \App\Services\Compliance\Forms\FormBService::class,
        'FORM_C' => \App\Services\Compliance\Forms\FormCService::class,
        'FORM_D' => \App\Services\Compliance\Forms\FormDService::class,
        'FORM_D_ER' => \App\Services\Compliance\Forms\FormDERService::class,
        'SHOPS_FORM_12' => \App\Services\Compliance\Forms\ShopsForm12Service::class,
        'SHOPS_FORM_13' => \App\Services\Compliance\Forms\ShopsForm13Service::class,
        'SHOPS_FORM_C' => \App\Services\Compliance\Forms\ShopsFormCService::class,
        'SHOPS_FORM_VI' => \App\Services\Compliance\Forms\ShopsFormVIService::class,
        'SHOPS_FINES' => \App\Services\Compliance\Forms\ShopsFinesService::class,
        'SHOPS_UNPAID' => \App\Services\Compliance\Forms\ShopsUnpaidService::class,
        'ESI_FORM_12' => \App\Services\Compliance\Forms\EsiForm12Service::class,
        'EPF_INSPECTION' => \App\Services\Compliance\Forms\EpfInspectionService::class,
        'HAZARD_REGISTER' => \App\Services\Compliance\Forms\HazardRegisterService::class,
    ];

    public function handle(): int
    {
        $tenantId = (int) $this->option('tenant');
        $branchId = (int) $this->option('branch');
        $month = $this->option('month') ?? now()->month;
        $year = $this->option('year') ?? now()->year;

        $this->info("Compliance Debug Report");
        $this->info("=======================");
        $this->info("Tenant ID: {$tenantId} | Branch ID: {$branchId} | Period: {$month}/{$year}");
        $this->newLine();

        // Verify tenant and branch exist
        if (!DB::table('tenants')->where('id', $tenantId)->exists()) {
            $this->error("Tenant {$tenantId} not found");
            return 1;
        }

        if (!DB::table('branches')->where('id', $branchId)->exists()) {
            $this->error("Branch {$branchId} not found");
            return 1;
        }

        $results = [];
        $successCount = 0;
        $nilCount = 0;
        $errorCount = 0;

        foreach ($this->formServices as $formName => $serviceClass) {
            try {
                $service = new $serviceClass();
                $response = $service->generate($tenantId, $branchId, (int) $month, (int) $year);

                $status = $response['status'] ?? 'UNKNOWN';
                $rowCount = count($response['rows'] ?? []);

                if ($status === 'SUCCESS') {
                    $successCount++;
                } elseif ($status === 'NIL') {
                    $nilCount++;
                }

                $results[] = [
                    'Form' => $formName,
                    'Status' => $status,
                    'Rows' => $rowCount,
                    'Error' => null,
                ];
            } catch (Exception $e) {
                $errorCount++;
                $results[] = [
                    'Form' => $formName,
                    'Status' => 'ERROR',
                    'Rows' => 0,
                    'Error' => $e->getMessage(),
                ];
            }
        }

        // Display results table
        $this->table(
            ['Form', 'Status', 'Rows', 'Error'],
            $results
        );

        // Summary
        $this->newLine();
        $this->info("Summary");
        $this->info("=======");
        $this->line("Total Forms: " . count($this->formServices));
        $this->line("✓ Success: {$successCount}");
        $this->line("○ Nil: {$nilCount}");
        $this->line("✗ Errors: {$errorCount}");

        return $errorCount > 0 ? 1 : 0;
    }
}
