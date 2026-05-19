<?php

namespace App\Services;

use App\Models\ComplianceExecutionBatch;
use App\Models\ManualComplianceMaster;
use App\Models\ManualComplianceBatchItem;
use Illuminate\Support\Facades\DB;

class ManualComplianceLoader
{
    public function load(ComplianceExecutionBatch $batch): void
    {
        $month = $batch->period_month;
        $tenantId = $batch->tenant_id;
        $branchId = $batch->branch_id;
        $batchId = $batch->id;

        $compliances = ManualComplianceMaster::query()
            ->where('is_automatable', false)
            ->where(function ($query) use ($month) {
                $query->where('frequency', 'monthly')
                    ->orWhere('frequency', 'event')
                    ->orWhere(function ($q) use ($month) {
                        $q->where('frequency', 'quarterly')
                            ->whereIn('due_month', [3, 6, 9, 12])
                            ->where('due_month', '<=', $month);
                    })
                    ->orWhere(function ($q) use ($month) {
                        $q->where('frequency', 'annual')
                            ->where('due_month', $month);
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
