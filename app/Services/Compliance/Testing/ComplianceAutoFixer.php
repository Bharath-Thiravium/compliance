<?php

namespace App\Services\Compliance\Testing;

use App\Models\Tenant;
use App\Models\Branch;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ComplianceAutoFixer
{
    public function fixAllIssues(): array
    {
        $fixes = [];

        $fixes['database'] = $this->fixDatabaseIssues();
        $fixes['generators'] = $this->fixMissingPrepareData();
        $fixes['blade_templates'] = $this->fixBladeTemplates();
        $fixes['directories'] = $this->fixDirectories();

        return $fixes;
    }

    private function fixDatabaseIssues(): array
    {
        $issues = [];

        $tenant = Tenant::first();
        if ($tenant) {
            $branch = Branch::where('tenant_id', $tenant->id)->first();
            if (!$branch) {
                $branch = Branch::create([
                    'tenant_id' => $tenant->id,
                    'branch_name' => 'Default Branch',
                    'unit_name' => 'Unit 1',
                    'address' => 'Default Address'
                ]);
                $issues[] = "Created missing branch for tenant {$tenant->id}";
            }
        }

        return $issues;
    }

    private function fixMissingPrepareData(): array
    {
        $issues = [];
        $generatorPath = app_path('Services/Compliance/FormGenerator');
        $files = File::files($generatorPath);

        $utilityClasses = [
            'BaseFormGenerator.php',
            'FormGeneratorFactory.php',
            'BladeMappingEngine.php',
            'FormDataAggregator.php',
            'FormValidationService.php'
        ];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (in_array($filename, $utilityClasses)) {
                continue;
            }

            $content = File::get($file->getPathname());
            
            if (strpos($content, 'prepareData') === false) {
                $this->addPrepareDataMethod($file->getPathname());
                $issues[] = "Added prepareData to {$filename}";
            }
        }

        return $issues;
    }

    private function addPrepareDataMethod(string $filePath): void
    {
        $content = File::get($filePath);

        $prepareDataMethod = <<<'PHP'

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        $records = $rawData['records'] ?? [];
        
        if (is_object($records)) {
            $records = $records->toArray();
        }

        foreach ($records as $record) {
            if (is_object($record)) {
                $record = (array) $record;
            }
            $rows[] = $record;
        }

        $totals = [];
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $totals[$key] = ($totals[$key] ?? 0) + $value;
                }
            }
        }

        return [
            'header' => [
                'form_title' => $rawData['form_title'] ?? 'Form',
                'period' => $rawData['period'] ?? '',
                'tenant' => $rawData['tenant'] ?? [],
                'branch' => $rawData['branch'] ?? [],
            ],
            'rows' => $rows,
            'totals' => $totals,
            'is_nil' => count($rows) === 0,
        ];
    }
PHP;

        if (strpos($content, 'class ') !== false && strpos($content, '{') !== false) {
            $lastBrace = strrpos($content, '}');
            if ($lastBrace !== false) {
                $content = substr_replace($content, $prepareDataMethod . "\n}", $lastBrace, 1);
                File::put($filePath, $content);
            }
        }
    }

    private function fixBladeTemplates(): array
    {
        $issues = [];
        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $modified = false;

            if (strpos($content, '@php') === false) {
                $phpBlock = "@php\n\$header = \$header ?? [];\n\$rows = \$rows ?? [];\n\$totals = \$totals ?? [];\n\$is_nil = \$is_nil ?? false;\n@endphp\n\n";
                
                if (strpos($content, '@extends') !== false) {
                    $content = preg_replace('/@extends\([^)]+\)/', "@extends('layouts.app')\n\n@section('content')\n" . $phpBlock, $content, 1);
                    $modified = true;
                } elseif (strpos($content, '@section') !== false) {
                    $content = preg_replace('/@section\([\'"]content[\'"]\)/', "@section('content')\n" . $phpBlock, $content, 1);
                    $modified = true;
                }
            }

            if ($modified) {
                File::put($file->getPathname(), $content);
                $issues[] = "Fixed template: {$file->getFilename()}";
            }
        }

        return $issues;
    }

    private function fixDirectories(): array
    {
        $issues = [];

        $directories = [
            'storage/app/compliance_pdfs',
            'storage/app/compliance_inspection_packs',
            'storage/app/generated_forms',
            'storage/app/temp'
        ];

        foreach ($directories as $dir) {
            $path = base_path($dir);
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                $issues[] = "Created directory: $dir";
            }
        }

        return $issues;
    }
}
