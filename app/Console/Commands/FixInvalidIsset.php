<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixInvalidIsset extends Command
{
    protected $signature = 'compliance:fix-invalid-isset';
    protected $description = 'Fix invalid isset() calls on expressions in blade templates';

    public function handle(): int
    {
        $this->info('🔧 Fixing Invalid isset() Calls...');
        $this->newLine();

        $templatePath = resource_path('views/compliance/forms');
        $templates = File::files($templatePath);

        $fixed = 0;

        foreach ($templates as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $original = $content;

            // Fix: isset($var['key'] ?? '') -> ($var['key'] ?? '')
            $content = preg_replace(
                '/isset\(\$(\w+)\[[\'"]([\w_]+)[\'"]\]\s*\?\?\s*[\'"](.*?)[\'"]\)\s*\?/',
                '($' . '$1[\'$2\'] ?? \'$3\') ?',
                $content
            );

            // Fix: isset($var ?? '') -> ($var ?? '')
            $content = preg_replace(
                '/isset\(\$(\w+)\s*\?\?\s*[\'"](.*?)[\'"]\)\s*\?/',
                '($' . '$1 ?? \'$2\') ?',
                $content
            );

            if ($content !== $original) {
                File::put($file->getPathname(), $content);
                $fixed++;
                $this->line("  ✓ Fixed: {$file->getFilename()}");
            }
        }

        $this->newLine();
        $this->info("✅ Fixed {$fixed} templates");
        return 0;
    }
}
