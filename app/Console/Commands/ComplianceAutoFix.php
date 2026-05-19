<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ComplianceAutoFix extends Command
{
    protected $signature = 'compliance:autofix';

    protected $description = 'Automatically fix common schema issues in Compliance Form Services';

    public function handle()
    {
        $servicePath = app_path('Services/Compliance/Forms');

        if (!File::exists($servicePath)) {
            $this->error("Forms directory not found.");
            return;
        }

        $files = File::files($servicePath);

        $replacements = [

            // payroll fixes
            'pe.basic_salary' => 'pe.wage_amount',

            // contractor deployment fixes
            'cl.worker_name' => 'e.name',

            // employee fixes
            'e.date_of_birth' => "''",

            // bonus table fixes
            'b.bonus_date' => 'b.payment_date',

        ];

        $fixed = 0;

        foreach ($files as $file) {

            $content = File::get($file);

            $original = $content;

            foreach ($replacements as $wrong => $correct) {

                $content = str_replace($wrong, $correct, $content);
            }

            if ($content !== $original) {

                File::put($file, $content);

                $this->info("Fixed: " . $file->getFilename());

                $fixed++;
            }
        }

        $this->info("\nAutoFix Complete");
        $this->info("Files Updated: $fixed");

        return 0;
    }
}
