<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AutoComplianceFormsSeeder extends Seeder
{
    public function run(): void
    {
        $path = resource_path('views/compliance/forms');

        $files = File::files($path);

        foreach ($files as $file) {

            $name = str_replace('.blade.php', '', $file->getFilename());

            DB::table('compliance_forms_master')->updateOrInsert(
                ['form_code' => strtoupper($name)],
                [
                    'section_id' => 1,
                    'form_name' => strtoupper(str_replace('_', ' ', $name)),
                    'act_type' => 'FACTORIES',
                    'frequency' => 'MONTHLY',
                    'due_day' => 7,
                    'due_month' => null,
                    'grace_days' => 0,
                    'priority' => 1,
                    'auto_generate' => 1,
                    'upload_only' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->info("Registered form: $name");
        }

        $this->command->info("All forms registered successfully.");
    }
}
