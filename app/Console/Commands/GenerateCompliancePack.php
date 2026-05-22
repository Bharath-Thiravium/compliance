<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ComplianceFormsMaster;
use App\Compliance\ComplianceDataService;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Support\Facades\File;

class GenerateCompliancePack extends Command
{
    protected $signature = 'compliance:generate-pack';
    protected $description = 'Generate full inspection pack with all 38 statutory compliance forms';

    public function handle(ComplianceDataService $dataService): int
    {
        $this->info("Starting Inspection Pack Generation...");
        
        // Find a suitable tenant with a FULL subscription, or any if not available
        $tenant = DB::table('tenants')->where('subscription_type', 'FULL')->orderBy('id', 'desc')->first();
        if (!$tenant) {
            $tenant = DB::table('tenants')->first();
        }
        
        if (!$tenant) {
            $this->error("No tenant found. Please run seeders first.");
            return 1;
        }

        $branch = DB::table('branches')->where('tenant_id', $tenant->id)->first();
        if (!$branch) {
            $this->error("No branch found for tenant {$tenant->id}");
            return 1;
        }

        $month = now()->month;
        $year = now()->year;
        
        $this->line("Target Tenant: {$tenant->name} (ID: {$tenant->id})");
        $this->line("Target Branch: {$branch->branch_name} (ID: {$branch->id})");
        $this->line("Period: {$month}/{$year}");
        $this->newLine();

        $forms = ComplianceFormsMaster::where('is_active', true)->get();
        $this->info("Found {$forms->count()} active forms to process.");

        if (\Illuminate\Support\Facades\File::exists(base_path('pack_errors.txt'))) {
            \Illuminate\Support\Facades\File::delete(base_path('pack_errors.txt'));
        }

        $tempDir = storage_path('app/temp_pack_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($forms as $form) {
            try {
                $html = $dataService->renderForm($form->form_code, $tenant->id, $branch->id, $month, $year);
                
                $fileName = $form->form_code . '.html';
                $filePath = $tempDir . '/' . $fileName;
                
                File::put($filePath, $html);
                $this->line("✅ Generated: {$fileName}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("❌ Failed: {$form->form_code} - " . $e->getMessage());
                \Illuminate\Support\Facades\File::append(base_path('pack_errors.txt'), "{$form->form_code}: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("Packaging forms...");

        $zipPath = base_path('compliance_pack.zip');
        
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = File::files($tempDir);
            foreach ($files as $file) {
                $zip->addFile($file->getPathname(), $file->getFilename());
            }
            $zip->close();
            
            $this->info("✅ Pack generated successfully at: compliance_pack.zip");
        } else {
            $this->error("❌ Failed to create ZIP archive.");
        }

        // Cleanup
        File::deleteDirectory($tempDir);

        $this->newLine();
        $this->line("Summary:");
        $this->line("  Successfully Generated: {$successCount}");
        $this->line("  Failed: {$failedCount}");
        
        return $failedCount === 0 ? 0 : 1;
    }
}
