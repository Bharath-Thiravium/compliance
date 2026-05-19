<?php

namespace App\Jobs;

use App\Services\Compliance\ComplianceReportBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateComplianceReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $batchId) {}

    public function handle(ComplianceReportBuilder $reportBuilder): void
    {
        $reportBuilder->generateFinalReport($this->batchId);
    }
}
