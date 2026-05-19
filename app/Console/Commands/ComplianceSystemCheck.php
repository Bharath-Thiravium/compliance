<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\Registry\FormTemplateRegistry;
use App\Services\Compliance\FormApis\FormApiServiceFactory;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\ComplianceOrchestrator;

class ComplianceSystemCheck extends Command
{
    protected $signature = 'compliance:system-check
        {--tenant_id=1}
        {--branch_id=1}
        {--month=1}
        {--year=2024}';

    protected $description = 'Run full compliance engine self test';

    public function handle()
    {
        $tenantId = $this->option('tenant_id');
        $branchId = $this->option('branch_id');
        $month = $this->option('month');
        $year = $this->option('year');

        $forms = FormTemplateRegistry::getAll();

        $results = [];

        foreach ($forms as $formCode => $template) {

            $status = "PASS";
            $issues = [];

            try {

                // API test
                $api = FormApiServiceFactory::make($formCode);
                $data = $api?->fetch($tenantId,$branchId,$month,$year);

                if (!isset($data['records'])) {
                    $status = "WARNING";
                    $issues[] = "API returned no records structure";
                }

                // Generator test
                $generator = FormGeneratorFactory::make($formCode);

                if ($generator) {
                    $formData = $generator->debugPrepareData($data);
                }

                // Template test
                if (!view()->exists($template)) {
                    $status = "ERROR";
                    $issues[] = "Template missing";
                }

                // Preview render test
                view($template, $formData ?? []);

                // PDF generation test
                app(ComplianceOrchestrator::class)
                    ->executePreview($formCode, $formData ?? []);

            } catch (\Throwable $e) {

                $status = "ERROR";
                $issues[] = $e->getMessage();

            }

            $results[] = [
                $formCode,
                $status,
                implode(" | ", $issues)
            ];
        }

        $this->table(
            ['Form','Status','Issues'],
            $results
        );

        $this->displayScore($results);

    }

    private function displayScore($results)
    {
        $total = count($results);

        $pass = collect($results)->where(1,'PASS')->count();
        $warning = collect($results)->where(1,'WARNING')->count();
        $error = collect($results)->where(1,'ERROR')->count();

        $score = round(($pass/$total)*100);

        $this->info("\nSystem Health Score: {$score}%");
        $this->line("PASS: {$pass}");
        $this->line("WARNING: {$warning}");
        $this->line("ERROR: {$error}");
    }
}
