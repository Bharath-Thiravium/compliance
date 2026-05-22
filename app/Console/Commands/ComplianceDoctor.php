<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use App\Compliance\ComplianceDataService;
use App\Compliance\Registry\FormRegistry;

class ComplianceDoctor extends Command
{
    protected $signature = 'compliance:doctor
                            {tenant=8}
                            {branch=9}
                            {month=1}
                            {year=2025}';

    protected $description = 'Scan entire compliance system and detect issues';

    public function handle()
    {
        $this->info("🔍 Starting Compliance System Diagnostic...\n");

        $tenant = $this->argument('tenant');
        $branch = $this->argument('branch');
        $month = $this->argument('month');
        $year = $this->argument('year');

        $forms = FormRegistry::all();
        $service = app(ComplianceDataService::class);

        $this->info("Total Forms Detected: " . count($forms));
        $this->line("------------------------------------");

        foreach ($forms as $form => $config) {

            $this->line("\nChecking Form: $form");

            /* ---------- Builder Check ---------- */

            $builderClass = $config['builder'];

            if (!class_exists($builderClass)) {
                $this->error("❌ Builder Missing: $builderClass");
                continue;
            }

            $this->info("✔ Builder Exists");

            /* ---------- Blade Check ---------- */

            $template = $config['template'];
            $blade = resource_path("views/" . str_replace('.', '/', $template) . ".blade.php");

            if (!File::exists($blade)) {
                $this->warn("⚠ Blade template missing");
            } else {
                $this->info("✔ Blade template exists");
            }

            /* ---------- Database Check ---------- */

            try {

                $data = $service->buildFormData(
                    $form,
                    $tenant,
                    $branch,
                    $month,
                    $year
                );

                if (isset($data['error'])) {
                    $this->error("❌ Builder Error: " . $data['error']);
                    continue;
                }

                if (isset($data['status']) && $data['status'] == 'NIL') {
                    $this->warn("⚠ NIL dataset");
                } else {
                    $this->info("✔ Data generation OK");
                }

            } catch (\Exception $e) {

                $this->error("❌ Exception: " . $e->getMessage());
            }

        }

        $this->line("\n------------------------------------");
        $this->info("Compliance Diagnostic Complete");
    }
}
