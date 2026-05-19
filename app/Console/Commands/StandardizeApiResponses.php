<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StandardizeApiResponses extends Command
{
    protected $signature = 'compliance:standardize-api-responses';
    protected $description = 'Standardize all API service responses to use records/meta structure';

    public function handle()
    {
        $apiPath = app_path('Services/Compliance/FormApis');
        $files = File::files($apiPath);

        $updated = 0;
        foreach ($files as $file) {
            if ($file->getFilename() === 'BaseFormApiService.php' || 
                $file->getFilename() === 'FormApiServiceFactory.php' ||
                $file->getFilename() === 'FormApiServices.php') {
                continue;
            }

            $content = $file->getContents();
            
            // Check if already standardized
            if (strpos($content, "'records' =>") !== false && strpos($content, "'meta' =>") !== false) {
                continue;
            }

            // Replace old response structure with new one
            $pattern = "/return \[\s*'tenant_id' => \$tenantId,\s*'branch_id' => \$branchId,\s*'month' => \$month,\s*'year' => \$year,\s*'period' => \$this->formatPeriod\(\),\s*'tenant' => \$this->getTenantDetails\(\$tenantId\),\s*'branch' => \$this->getBranchDetails\(\$branchId, \$tenantId\),\s*'rows' => \$rows,\s*'record_count' => count\(\$rows\),\s*\];/s";

            $replacement = "return [\n            'records' => \$rows,\n            'meta' => [\n                'tenant_id' => \$tenantId,\n                'branch_id' => \$branchId,\n                'month' => \$month,\n                'year' => \$year,\n            ],\n            'tenant' => \$this->getTenantDetails(\$tenantId),\n            'branch' => \$this->getBranchDetails(\$branchId, \$tenantId),\n            'period' => \$this->formatPeriod(),\n        ];";

            $newContent = preg_replace($pattern, $replacement, $content);

            if ($newContent !== $content) {
                File::put($file->getPathname(), $newContent);
                $this->info("✓ Updated: " . $file->getFilename());
                $updated++;
            }
        }

        $this->info("\n✅ Standardized {$updated} API services");
    }
}
