<?php

namespace App\Services;

use App\Models\ComplianceExecutionBatch;
use App\Models\ManualComplianceMaster;
use App\Models\ManualComplianceBatchItem;

class ManualComplianceLoader
{
    public function load(ComplianceExecutionBatch $batch): void
    {
        $month = $batch->period_month;
        $tenantId = $batch->tenant_id;
        $branchId = $batch->branch_id;
        $batchId = $batch->id;

        $isQuarterEnd  = in_array($month, [3, 6, 9, 12]);
        $isHalfYearEnd = in_array($month, [6, 12]);
        $isYearEnd     = ($month === 12);

        $compliances = ManualComplianceMaster::query()
            ->where('is_automatable', false)
            ->where(function ($query) use ($month, $isQuarterEnd, $isHalfYearEnd, $isYearEnd) {
                // Monthly and event-based tasks apply every period
                $query->whereIn('frequency', ['monthly', 'event']);

                // Quarterly tasks apply at end-of-quarter months: 3, 6, 9, 12
                if ($isQuarterEnd) {
                    $query->orWhere('frequency', 'quarterly');
                }

                // Half-yearly tasks apply in June and December
                if ($isHalfYearEnd) {
                    $query->orWhere('frequency', 'half_yearly');
                }

                // Annual tasks: apply in the specified due_month,
                // or in December when due_month is not configured
                $query->orWhere(function ($q) use ($month, $isYearEnd) {
                    $q->where('frequency', 'annual')
                        ->where(function ($cond) use ($month, $isYearEnd) {
                            $cond->where('due_month', $month);
                            if ($isYearEnd) {
                                $cond->orWhereNull('due_month');
                            }
                        });
                });
            })
            ->get();

        $items = $compliances->map(fn($compliance) => [
            'batch_id' => $batchId,
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'compliance_id' => $compliance->id,
            'status' => 'pending',
            'document_path' => null,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        if (!empty($items)) {
            ManualComplianceBatchItem::insert($items);
        }
    }
}
