<?php

namespace App\Services\Compliance;

use App\Models\ManualComplianceMaster;
use App\Models\ManualComplianceBatchItem;
use App\Models\ComplianceExecutionBatch;
use Illuminate\Support\Facades\DB;

class ManualComplianceLoaderService
{
    public function loadForBatch(ComplianceExecutionBatch $batch): void
    {
        $month = $batch->period_month;
        $compliances = $this->getApplicableCompliances($month);

        $items = [];
        foreach ($compliances as $compliance) {
            $items[] = [
                'batch_id' => $batch->id,
                'tenant_id' => $batch->tenant_id,
                'branch_id' => $batch->branch_id,
                'compliance_id' => $compliance->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($items)) {
            DB::table('compliance_manual_batch_items')->insert($items);
        }
    }

    private function getApplicableCompliances(int $month)
    {
        $quarterlyMonths = [3, 6, 9, 12];

        return ManualComplianceMaster::where('is_automatable', false)
            ->where(function ($query) use ($month, $quarterlyMonths) {
            $query->where('frequency', 'monthly')
                ->orWhere('frequency', 'event')
                ->orWhere(function ($q) use ($month) {
                    $q->where('frequency', 'annual')
                        ->where('due_month', $month);
                })
                ->orWhere(function ($q) use ($quarterlyMonths) {
                    $q->where('frequency', 'quarterly')
                        ->whereIn('due_month', $quarterlyMonths);
                });
        })->get();
    }
}
