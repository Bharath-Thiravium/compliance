<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TestComplianceGeneration extends Command
{
    protected $signature = 'compliance:test-generation {--all : Test all 36 forms}';
    protected $description = 'Test statutory form generation';

    public function handle()
    {
        ini_set('memory_limit', '512M');
        
        $this->info('Testing Form Generation with Wage Validation...');

        $tenant = DB::table('tenants')->where('subscription_type', 'FULL')->orderBy('id', 'desc')->first();
        if (!$tenant) {
            $this->error('No FULL tenant found. Run seeder first.');
            return 1;
        }
        
        $branch = DB::table('branches')->where('tenant_id', $tenant->id)->first();
        if (!$branch) {
            $this->error('No branch found. Run seeder first.');
            return 1;
        }

        $this->line("Tenant: {$tenant->name} (ID: {$tenant->id})");
        $this->line("Branch: {$branch->branch_name} (ID: {$branch->id})");
        
        $this->info('Running pre-generation data repair...');
        $this->call('compliance:repair-payroll-data', [
            'tenant_id' => $tenant->id,
            'month' => 1,
            'year' => 2026,
        ]);
        $this->newLine();

        $testForms = $this->option('all') 
            ? FormGeneratorFactory::getSupportedForms()
            : ['FORM_B', 'FORM_XIII', 'ESI_FORM_12', 'EPF_INSPECTION'];

        $success = 0;
        $failed = 0;
        $failedForms = [];
        $formStats = [];
        $batchId = time();
        $startTime = microtime(true);

        foreach ($testForms as $formCode) {
            $formStart = microtime(true);
            $memBefore = memory_get_usage(true);
            
            try {
                $generator = FormGeneratorFactory::make($formCode);
                
                if (!$generator) {
                    $this->warn("⚠️  {$formCode}: No generator found");
                    $failed++;
                    $failedForms[] = ['code' => $formCode, 'error' => 'No generator'];
                    continue;
                }

                $filePath = $generator->generate($tenant->id, $branch->id, 1, 2026, $batchId);
                
                $formEnd = microtime(true);
                $memAfter = memory_get_usage(true);
                $memUsed = round(($memAfter - $memBefore) / 1024 / 1024, 2);
                $timeUsed = round($formEnd - $formStart, 2);
                
                if (Storage::disk('local')->exists($filePath)) {
                    $size = Storage::disk('local')->size($filePath);
                    $this->info("✅ {$formCode}: " . number_format($size) . " bytes | {$timeUsed}s | {$memUsed}MB");
                    $success++;
                    $formStats[$formCode] = ['status' => 'success', 'time' => $timeUsed, 'memory' => $memUsed];
                } else {
                    $this->error("❌ {$formCode}: File not created | {$timeUsed}s | {$memUsed}MB");
                    $failed++;
                    $failedForms[] = ['code' => $formCode, 'error' => 'File not created', 'time' => $timeUsed, 'memory' => $memUsed];
                }
            } catch (\Throwable $e) {
                $formEnd = microtime(true);
                $memAfter = memory_get_usage(true);
                $memUsed = round(($memAfter - $memBefore) / 1024 / 1024, 2);
                $timeUsed = round($formEnd - $formStart, 2);
                
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'memory') !== false) {
                    $errorMsg = 'MEMORY ERROR: ' . $errorMsg;
                }
                
                $this->error("❌ {$formCode}: {$errorMsg} | {$timeUsed}s | {$memUsed}MB");
                $failed++;
                $failedForms[] = ['code' => $formCode, 'error' => $errorMsg, 'time' => $timeUsed, 'memory' => $memUsed];
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $this->newLine();
        $this->info("Success: {$success}/" . count($testForms) . " | Failed: {$failed}/" . count($testForms));
        $this->info("Total Time: {$duration}s | Peak Memory: {$peakMemory}MB");
        
        if (!empty($failedForms)) {
            $this->newLine();
            $this->error("Failed Forms:");
            foreach ($failedForms as $fail) {
                $time = $fail['time'] ?? 'N/A';
                $mem = $fail['memory'] ?? 'N/A';
                $this->line("  - {$fail['code']}: {$fail['error']} | {$time}s | {$mem}MB");
            }
        }

        return $failed === 0 ? 0 : 1;
    }
}
