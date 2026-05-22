<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Models\ManualComplianceBatchItem;
use App\Services\Compliance\ManualComplianceExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManualComplianceExecutionController extends Controller
{
    public function __construct(private ManualComplianceExecutionService $service) {}

    public function getBatchCompliances(int $batchId): JsonResponse
    {
        // findBatchForCurrentUser already enforces tenant+branch scope.
        // Re-use those values directly — no second round-trip needed.
        $this->findBatchForCurrentUser($batchId);
        $user = auth()->user();

        $items = ManualComplianceBatchItem::query()
            ->where('batch_id', $batchId)
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->join('compliance_manual_master', 'compliance_manual_batch_items.compliance_id', '=', 'compliance_manual_master.id')
            ->select([
                'compliance_manual_batch_items.id as item_id',
                'compliance_manual_master.compliance_name',
                'compliance_manual_master.act_name',
                'compliance_manual_batch_items.status',
                'compliance_manual_batch_items.compliance_result',
                'compliance_manual_batch_items.document_path',
                'compliance_manual_batch_items.file_size',
                'compliance_manual_batch_items.uploaded_at',
            ])
            ->get();

        return response()->json(['batch_id' => $batchId, 'items' => $items]);
    }

    public function complete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'item_id' => 'required|integer|exists:compliance_manual_batch_items,id',
                'file'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        try {
            $item = $this->findItemForCurrentUser($validated['item_id']);
            $this->service->complete($item, $request->file('file'), auth()->id());
            $item->refresh();
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Upload failed. Please try again.'], 500);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Document uploaded and marked as completed.',
            'item_id'       => $item->id,
            'document_path' => $item->document_path,
            'file_size'     => $item->file_size,
        ]);
    }

    public function skip(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'item_id' => 'required|integer|exists:compliance_manual_batch_items,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        try {
            $item = $this->findItemForCurrentUser($validated['item_id']);
            $this->service->skip($item);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Skip failed. Please try again.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Compliance marked as skipped.']);
    }

    public function serveDocument(Request $request, int $itemId): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        try {
            $item = $this->findItemForCurrentUser($itemId);
        } catch (\Exception $e) {
            abort(404, 'Document not found.');
        }

        if (! $item->document_path) {
            abort(404, 'No document attached to this item.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($item->document_path)) {
            abort(404, 'Document file not found.');
        }

        return $disk->download($item->document_path);
    }

    // -------------------------------------------------------------------------
    // Helpers — all DB fetches are scoped to the authenticated user's tenant+branch
    // -------------------------------------------------------------------------

    private function findBatchForCurrentUser(int $batchId): ComplianceExecutionBatch
    {
        $user = auth()->user();


        $batch = ComplianceExecutionBatch::where('id', $batchId)
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->first();

        if (! $batch) {
            // Important: for JSON endpoints return JSON. For non-JSON, keep default HTML.
            $request = request();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
            }

            abort(404, 'Batch not found.');
        }


        return $batch;
    }

    private function findItemForCurrentUser(int $itemId): ManualComplianceBatchItem
    {
        $user = auth()->user();


        Log::debug('ManualCompliance: findItemForCurrentUser', [
            'item_id'   => $itemId,
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id,
        ]);

        $q = ManualComplianceBatchItem::where('id', $itemId)
            ->where('tenant_id', $user->tenant_id);
        if ($user->branch_id !== null) {
            $q->where('branch_id', $user->branch_id);
        }
        $item = $q->first();

        if (! $item) {
            $raw = ManualComplianceBatchItem::find($itemId);

            Log::error('ManualCompliance: item not found for user scope', [
                'item_id'        => $itemId,

                'user_tenant_id' => $user->tenant_id,
                'user_branch_id' => $user->branch_id,
                'item_tenant_id' => $raw?->tenant_id,
                'item_branch_id' => $raw?->branch_id,
                'item_exists'    => (bool) $raw,
            ]);
            abort(404, 'Item not found or access denied.');
        }

        return $item;
    }
}
