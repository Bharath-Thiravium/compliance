<?php

namespace App\Services\Compliance;

use App\Models\ManualComplianceMaster;
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
        $isQuarterEnd  = in_array($month, [3, 6, 9, 12]);
        $isHalfYearEnd = in_array($month, [6, 12]);
        $isYearEnd     = ($month === 12);

        return ManualComplianceMaster::where('is_automatable', false)
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
    }
}
