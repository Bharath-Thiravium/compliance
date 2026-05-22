<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\BatchInspectionPackService;

class CreateInspectionPackCommand extends Command
{
    protected $signature = 'compliance:create-inspection-pack
                            {--tenant_id=1 : Tenant ID}
                            {--branch_id=1 : Branch ID}
                            {--month=1 : Month (1-12)}
                            {--year=2025 : Year}
                            {--forms= : Comma-separated form codes (optional)}';

    protected $description = 'Create an inspection pack (ZIP) with all compliance forms for a period';

    public function handle(BatchInspectionPackService $packService)
    {
        $tenantId = (int) $this->option('tenant_id');
        $branchId = (int) $this->option('branch_id');
        $month = (int) $this->option('month');
        $year = (int) $this->option('year');
        $formCodes = $this->option('forms') ? explode(',', $this->option('forms')) : null;

        $this->info('🔄 Creating inspection pack...');
        $this->info("   Tenant: {$tenantId}");
        $this->info("   Branch: {$branchId}");
        $this->info("   Period: {$month}/{$year}");

        try {
            $zipPath = $packService->createInspectionPack(
                $tenantId,
                $branchId,
                $month,
                $year,
                $formCodes
            );

            $this->info('');
            $this->info('✅ Inspection pack created successfully!');
            $this->info('');
            $this->info('📦 Pack Details:');
            $this->info('   File: ' . basename($zipPath));
            $this->info('   Path: ' . $zipPath);
            $this->info('   Size: ' . $this->formatBytes(filesize($zipPath)));
            $this->info('');
            $this->info('📥 Download URL:');
            $this->info('   ' . route('compliance.download-pack', ['file' => basename($zipPath)]));
            $this->info('');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
