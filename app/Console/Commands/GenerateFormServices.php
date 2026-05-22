<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Services\Compliance\FormGenerator\BladeMappingEngine;

class GenerateFormServices extends Command
{
    protected $signature = 'compliance:generate-form-services {--force : Overwrite existing services}';
    protected $description = 'Generate form services from blade templates using automatic mapping';

    public function handle()
    {
        $engine = new BladeMappingEngine();
        $bladeDir = resource_path('views/compliance/forms');
        $serviceDir = app_path('Services/Compliance/Forms');
        $existingServices = $this->getExistingServices($serviceDir);

        $bladeFiles = File::files($bladeDir);
        $generated = 0;
        $skipped = 0;

        foreach ($bladeFiles as $file) {
            if ($file->getExtension() !== 'php' || strpos($file->getFilename(), 'reference') !== false) {
                continue;
            }

            $bladeContent = $file->getContents();
            $formCode = $engine->getFormCode($file->getFilename());
            $serviceClass = $this->getServiceClassName($formCode);
            $servicePath = "$serviceDir/{$serviceClass}.php";

            if (File::exists($servicePath) && !$this->option('force')) {
                $this->line("Skipping {$serviceClass} (already exists)");
                $skipped++;
                continue;
            }

            $columns = $engine->extractColumns($bladeContent);
            if (empty($columns)) {
                $this->warn("No columns found in {$file->getFilename()}");
                continue;
            }

            $serviceCode = $this->generateServiceCode($serviceClass, $formCode, $columns);
            File::put($servicePath, $serviceCode);

            $this->info("Generated {$serviceClass}");
            $generated++;
        }

        $this->info("\nGeneration complete: {$generated} created, {$skipped} skipped");
    }

    protected function getExistingServices(string $dir): array
    {
        $services = [];
        foreach (File::files($dir) as $file) {
            if ($file->getExtension() === 'php' && $file->getFilename() !== 'BaseFormService.php') {
                $services[] = $file->getFilename();
            }
        }
        return $services;
    }

    protected function getServiceClassName(string $formCode): string
    {
        $parts = explode(' ', $formCode);
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst(strtolower($part));
        }
        return $className . 'Service';
    }

    protected function generateServiceCode(string $className, string $formCode, array $columns): string
    {
        $columnMappings = $this->buildColumnMappings($columns);

        return <<<PHP
<?php

namespace App\\Services\\Compliance\\Forms;

use Illuminate\\Support\\Facades\\DB;
use App\\Services\\Compliance\\Debug\\FormDebugger;

class {$className} extends BaseFormService
{
    public function generate(int \$tenantId, int \$branchId, int \$month, int \$year): array
    {
        FormDebugger::start('{$formCode}');

        \$this->tenantId = \$tenantId;
        \$this->branchId = \$branchId;
        \$this->month = \$month;
        \$this->year = \$year;

        [\$startDate, \$endDate] = \$this->getDateRange();

        \$rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', \$tenantId)
            ->where('e.branch_id', \$branchId)
            ->select([
{$columnMappings}
            ])
            ->get()
            ->map(fn(\$row) => (array)\$row)
            ->toArray();

        FormDebugger::end('{$formCode}', \$rows);

        \$tenant = DB::table('tenants')->where('id', \$tenantId)->first();

        \$header = [
            'tenant' => [
                'name' => \$tenant?->name ?? 'N/A',
            ],
            'period' => date('F Y', strtotime("\$year-\$month-01")),
        ];

        if (empty(\$rows)) {
            return [
                'header' => \$header,
                'rows' => [],
                'is_nil' => true,
                'totals' => []
            ];
        }

        return [
            'header' => \$header,
            'rows' => \$rows,
            'is_nil' => false,
            'totals' => []
        ];
    }
}
PHP;
    }

    protected function buildColumnMappings(array $columns): string
    {
        $mappings = [];
        foreach ($columns as $column) {
            $mappings[] = "                'e.{$column} as {$column}',";
        }
        return implode("\n", $mappings);
    }
}
