<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBladeTemplates extends Command
{
    protected $signature = 'compliance:fix-blade-templates';
    protected $description = 'Fix blade templates with missing variable handling';

    public function handle(): int
    {
        $this->info('🔧 Fixing Blade Templates with Missing Variables...');
        $this->newLine();

        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);

        $fixed = 0;
        $skipped = 0;

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $hasData = strpos($content, '@if') !== false || 
                      strpos($content, '@forelse') !== false || 
                      strpos($content, '@foreach') !== false;

            if (!$hasData) {
                $this->fixTemplate($file->getPathname());
                $fixed++;
                $this->line("  ✓ Fixed: {$file->getFilename()}");
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("✅ Fixed {$fixed} templates, skipped {$skipped}");
        return 0;
    }

    private function fixTemplate(string $filePath): void
    {
        $content = File::get($filePath);
        $filename = basename($filePath);

        // Add safe variable handling based on template type
        if (strpos($content, '@extends') === false) {
            // Add layout if missing
            $content = "@extends('compliance.layouts.preview')\n\n" . $content;
        }

        // Ensure rows section has safe iteration
        if (strpos($content, '$rows') !== false && strpos($content, '@forelse') === false && strpos($content, '@foreach') === false) {
            $content = str_replace(
                '$rows',
                '@forelse($rows ?? [] as $row)
                    {{-- Row data --}}
                @empty
                    <p>No data available</p>
                @endforelse',
                $content
            );
        }

        // Add safe fallbacks for common variables
        $safeVariables = [
            '$header' => '$header ?? []',
            '$totals' => '$totals ?? []',
            '$is_nil' => '$is_nil ?? false',
            '$form_title' => '$form_title ?? "Form"',
            '$form_code' => '$form_code ?? ""',
        ];

        foreach ($safeVariables as $var => $safe) {
            if (strpos($content, $var) !== false && strpos($content, $safe) === false) {
                $content = str_replace($var, $safe, $content);
            }
        }

        // Ensure all array accesses have null coalescing
        $content = preg_replace_callback(
            '/\$(\w+)\[[\'"]([\w_]+)[\'"]\](?!\s*\?\?)/',
            function ($matches) {
                return "\${$matches[1]}['{$matches[2]}'] ?? ''";
            },
            $content
        );

        File::put($filePath, $content);
    }
}
