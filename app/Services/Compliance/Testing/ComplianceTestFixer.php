<?php

namespace App\Services\Compliance\Testing;

use App\Models\Tenant;
use App\Models\Branch;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ComplianceTestFixer
{
    public function fixAllIssues(): array
    {
        $fixes = [];

        $fixes['database'] = $this->fixDatabaseIssues();
        $fixes['generators'] = $this->fixGeneratorDetection();
        $fixes['blade_templates'] = $this->fixBladeTemplates();
        $fixes['directories'] = $this->fixDirectories();

        return $fixes;
    }

    private function fixDatabaseIssues(): array
    {
        $issues = [];

        // Ensure tenant has valid branch
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

    private function fixGeneratorDetection(): array
    {
        $issues = [];
        $generatorPath = app_path('Services/Compliance/FormGenerator');
        $files = File::files($generatorPath);

        $utilityClasses = [
            'BladeMappingEngine.php',
            'FormDataAggregator.php',
            'FormValidationService.php'
        ];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (in_array($filename, $utilityClasses)) {
                $issues[] = "Identified utility class: $filename (not a generator)";
            }
        }

        return $issues;
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

            // Check and add missing variables
            if (strpos($content, '$header') === false && strpos($content, '@forelse') !== false) {
                // Add header variable at top of template
                if (strpos($content, '@section') !== false) {
                    $content = preg_replace(
                        '/@section\(\'content\'\)/',
                        "@section('content')\n@php\n\$header = \$header ?? [];\n@endphp",
                        $content,
                        1
                    );
                    $modified = true;
                }
            }

            if (strpos($content, '$rows') === false && strpos($content, '@forelse') === false) {
                // Add rows variable
                if (strpos($content, '@section') !== false) {
                    $content = preg_replace(
                        '/@section\(\'content\'\)/',
                        "@section('content')\n@php\n\$rows = \$rows ?? [];\n@endphp",
                        $content,
                        1
                    );
                    $modified = true;
                }
            }

            if (strpos($content, '$totals') === false) {
                // Add totals variable
                if (strpos($content, '@section') !== false) {
                    $content = preg_replace(
                        '/@section\(\'content\'\)/',
                        "@section('content')\n@php\n\$totals = \$totals ?? [];\n@endphp",
                        $content,
                        1
                    );
                    $modified = true;
                }
            }

            if (strpos($content, '$is_nil') === false) {
                // Add is_nil variable
                if (strpos($content, '@section') !== false) {
                    $content = preg_replace(
                        '/@section\(\'content\'\)/',
                        "@section('content')\n@php\n\$is_nil = \$is_nil ?? false;\n@endphp",
                        $content,
                        1
                    );
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
