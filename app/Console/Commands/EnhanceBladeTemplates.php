<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EnhanceBladeTemplates extends Command
{
    protected $signature = 'compliance:enhance-blade-templates';
    protected $description = 'Enhance blade templates with comprehensive null coalescing';

    public function handle(): int
    {
        $this->info('🔧 Enhancing Blade Templates with Null Coalescing...');
        $this->newLine();

        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);

        $enhanced = 0;

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $original = $content;

            // Add null coalescing to all variable accesses
            $content = $this->addNullCoalescing($content);

            if ($content !== $original) {
                File::put($file->getPathname(), $content);
                $enhanced++;
                $this->line("  ✓ Enhanced: {$file->getFilename()}");
            }
        }

        $this->newLine();
        $this->info("✅ Enhanced {$enhanced} templates");
        return 0;
    }

    private function addNullCoalescing(string $content): string
    {
        // Fix array access without null coalescing: $var['key'] -> $var['key'] ?? ''
        $content = preg_replace_callback(
            '/\$(\w+)\[[\'"]([\w_]+)[\'"]\](?!\s*\?\?)/',
            function ($matches) {
                return "\${$matches[1]}['{$matches[2]}'] ?? ''";
            },
            $content
        );

        // Fix variable access without null coalescing: {{ $var }} -> {{ $var ?? '' }}
        $content = preg_replace_callback(
            '/\{\{\s*\$(\w+)\s*\}\}(?!\s*\?\?)/',
            function ($matches) {
                return "{{ \${$matches[1]} ?? '' }}";
            },
            $content
        );

        // Fix nested array access: $var['key1']['key2'] -> $var['key1']['key2'] ?? ''
        $content = preg_replace_callback(
            '/\$(\w+)\[[\'"]([\w_]+)[\'"]\]\[[\'"]([\w_]+)[\'"]\](?!\s*\?\?)/',
            function ($matches) {
                return "\${$matches[1]}['{$matches[2]}']['{$matches[3]}'] ?? ''";
            },
            $content
        );

        // Fix number_format calls with missing null coalescing
        $content = preg_replace_callback(
            '/number_format\(\s*\$(\w+)\[[\'"]([\w_]+)[\'"]\]\s*(?!.*\?\?)/',
            function ($matches) {
                return "number_format(\${$matches[1]}['{$matches[2]}'] ?? 0";
            },
            $content
        );

        // Ensure @forelse has proper fallback
        $content = preg_replace(
            '/@forelse\(\$(\w+)\s+as\s+/',
            '@forelse($' . '$1 ?? [] as ',
            $content
        );

        return $content;
    }
}
