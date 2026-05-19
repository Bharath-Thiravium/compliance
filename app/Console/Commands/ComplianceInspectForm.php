<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\Forms\FormXIIService;
use App\Services\Compliance\Forms\FormXIIIService;
use App\Services\Compliance\Forms\FormXIVService;
use App\Services\Compliance\Forms\FormXVIService;
use App\Services\Compliance\Forms\FormXVIIService;
use App\Services\Compliance\Forms\FormXXIIIService;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;

class ComplianceInspectForm extends Command
{
    protected $signature = 'compliance:inspect {form} {--tenant=1} {--branch=1} {--month=} {--year=}';
    protected $description = 'Inspect statutory form data generation';

    public function handle()
    {
        $form = strtoupper($this->argument('form'));
        $tenantId = $this->option('tenant');
        $branchId = $this->option('branch');
        $month = $this->option('month') ?? now()->month;
        $year = $this->option('year') ?? now()->year;

        $services = [
            'FORM_XII' => FormXIIService::class,
            'FORM_XIII' => FormXIIIService::class,
            'FORM_XIV' => FormXIVService::class,
            'FORM_XVI' => FormXVIService::class,
            'FORM_XVII' => FormXVIIService::class,
            'FORM_XXIII' => FormXXIIIService::class,
        ];

        $data = null;

        if (isset($services[$form])) {
            try {
                $service = new $services[$form]();
                $data = $service->generate($tenantId, $branchId, $month, $year);
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                return 1;
            }
        } else {
            // Try FormGeneratorFactory for modern generators
            $generator = FormGeneratorFactory::make($form);

            if (!$generator) {
                $supported = array_merge(array_keys($services), FormGeneratorFactory::getSupportedForms());
                $this->error("Form {$form} not found. Available: " . implode(', ', array_unique($supported)));
                return 1;
            }

            try {
                $data = $generator->getData($tenantId, $branchId, $month, $year);
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                return 1;
            }
        }

        $this->info("✓ {$form} Data Generated Successfully");
        $this->line('');
        $this->line('Header:');
        $this->table(['Key', 'Value'], $this->flattenArray($data['header']), 'compact', [], []);

        $this->line('');
        $this->line("Rows: " . count($data['rows']) . " records");

        if (!empty($data['rows'])) {
            $this->table(array_keys($data['rows'][0]), array_slice($data['rows'], 0, 3), 'compact', [], []);
            if (count($data['rows']) > 3) {
                $this->line("... and " . (count($data['rows']) - 3) . " more rows");
            }
        }

        if (!empty($data['totals'])) {
            $this->line('');
            $this->line('Totals:');
            $this->table(['Key', 'Value'], $this->flattenArray($data['totals']), 'compact', [], []);
        }

        return 0;
    }

    private function flattenArray(array $arr, string $prefix = ''): array
    {
        $result = [];
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[] = [$prefix . $key, $value];
            }
        }
        return $result;
    }
}
