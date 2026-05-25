<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Models\ManualComplianceBatchItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ComplianceDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user     = auth()->user();
        $branchId = $request->query('branch_id') !== null ? (int) $request->query('branch_id') : $user->branch_id;

        $batches = $this->batchQuery($user->tenant_id, $branchId)
            ->orderByDesc('created_at')
            ->get();

        return view('compliance.manual_dashboard', [
            'batches'      => $batches,
            'currentBatch' => $batches->first(),
            'tenantId'     => $user->tenant_id,
            'branchId'     => $branchId,
        ]);
    }

    public function getBatchSummary(int $batchId): JsonResponse
    {
        try {
            $user  = auth()->user();
            $batch = $this->findBatch($batchId, $user->tenant_id, $user->branch_id);

            $branchId = $user->branch_id ?? $batch->branch_id;

            $summary = $this->itemQuery($batchId, $user->tenant_id, $branchId)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                    DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                    DB::raw("SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped")
                )
                ->first();

            $total     = (int) ($summary->total     ?? 0);
            $completed = (int) ($summary->completed ?? 0);

            return response()->json([
                'batch_id'   => $batchId,
                'month'      => $batch->period_month,
                'year'       => $batch->period_year,
                'total'      => $total,
                'completed'  => $completed,
                'pending'    => (int) ($summary->pending ?? 0),
                'skipped'    => (int) ($summary->skipped ?? 0),
                'percentage' => $total > 0 ? (int) round($completed / $total * 100) : 0,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load batch summary.'], 500);
        }
    }

    public function getTenantBatches(): JsonResponse
    {
        try {
            $user = auth()->user();

            $batches = $this->batchQuery($user->tenant_id, $user->branch_id)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($batch) use ($user) {
                    $branchId = $user->branch_id ?? $batch->branch_id;

                    $summary = $this->itemQuery($batch->id, $user->tenant_id, $branchId)
                        ->select(
                            DB::raw('COUNT(*) as total'),
                            DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                            DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
                        )
                        ->first();

                    return [
                        'batch_id'    => $batch->id,
                        'month'       => $batch->period_month,
                        'year'        => $batch->period_year,
                        'total_tasks' => (int) ($summary->total     ?? 0),
                        'completed'   => (int) ($summary->completed ?? 0),
                        'pending'     => (int) ($summary->pending   ?? 0),
                    ];
                });

            return response()->json($batches);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load batches.'], 500);
        }
    }

    public function getBatchItems(int $batchId): JsonResponse
    {
        try {
            $user  = auth()->user();
            $batch = $this->findBatch($batchId, $user->tenant_id, $user->branch_id);

            // Use the branch stored on the batch when the user has no branch assigned
            $branchId = $user->branch_id ?? $batch->branch_id;

            $items = $this->itemQuery($batchId, $user->tenant_id, $branchId)
                ->join('compliance_manual_master', 'compliance_manual_batch_items.compliance_id', '=', 'compliance_manual_master.id')
                ->select([
                    'compliance_manual_batch_items.id as item_id',
                    'compliance_manual_master.compliance_name',
                    'compliance_manual_master.act_name',
                    'compliance_manual_batch_items.status',
                    'compliance_manual_batch_items.document_path',
                    'compliance_manual_batch_items.file_size',
                    'compliance_manual_batch_items.uploaded_at',
                ])
                ->get();

            return response()->json(['batch_id' => $batchId, 'items' => $items]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------

    public function getTimelineStatus(int $batchId): JsonResponse
    {
        try {
            $user     = auth()->user();
            $batch    = $this->findBatch($batchId, $user->tenant_id, $user->branch_id);
            $branchId = $user->branch_id ?? $batch->branch_id;

            // ── Manual items ──────────────────────────────────────────────────
            $manualBase = $this->itemQuery($batchId, $user->tenant_id, $branchId);

            $manualStats = (clone $manualBase)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending"),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
                )
                ->first();

            // Overdue: pending items whose batch period has already passed
            $batchPeriodEnd = \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)
                ->endOfMonth();
            $overdue = now()->gt($batchPeriodEnd)
                ? (clone $manualBase)->where('status', 'pending')->count()
                : 0;

            // ── Automated forms ───────────────────────────────────────────────
            $autoBase = DB::table('compliance_batch_forms')
                ->where('batch_id', $batchId)
                ->where('tenant_id', $user->tenant_id);

            $autoTotal = (clone $autoBase)->count();
            $generated = (clone $autoBase)->where('status', 'success')->count();

            // ── Totals ────────────────────────────────────────────────────────
            $manualTotal   = (int) ($manualStats->total   ?? 0);
            $manualPending = (int) ($manualStats->pending ?? 0);

            return response()->json([
                'total'     => $manualTotal + $autoTotal,
                'pending'   => $manualPending + ($autoTotal - $generated),
                'generated' => $generated,
                'verified'  => 0, // reserved — no verified_at column yet
                'overdue'   => $overdue,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Batch not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load timeline status.'], 500);
        }
    }

    private function batchQuery(?int $tenantId, ?int $branchId)
    {
        if ($tenantId === null) return ComplianceExecutionBatch::whereRaw('0=1');
        $q = ComplianceExecutionBatch::where('tenant_id', $tenantId);
        if ($branchId !== null) {
            $q->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
        return $q;
    }

    private function findBatch(int $batchId, ?int $tenantId, ?int $branchId): ComplianceExecutionBatch
    {
        if ($tenantId === null) throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        $q = ComplianceExecutionBatch::where('id', $batchId)->where('tenant_id', $tenantId);
        if ($branchId !== null) {
            $q->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
        return $q->firstOrFail();
    }

    private function itemQuery(int $batchId, ?int $tenantId, ?int $branchId)
    {
        if ($tenantId === null) return ManualComplianceBatchItem::whereRaw('0=1');
        $q = ManualComplianceBatchItem::where('batch_id', $batchId)
            ->where('tenant_id', $tenantId);
        if ($branchId !== null) {
            $q->where('branch_id', $branchId);
        }
        return $q;
    }
}
