<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompliancePreviewController extends Controller
{
    public function __construct(private ComplianceOrchestrator $orchestrator) {}

    public function preview(Request $request, string $formCode)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id;
            $branchId = $request->get('branch_id', $user->branch_id ?? null);
            $batchId = $request->get('batch_id');

            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            // Validate batch if provided
            if ($batchId) {
                $batch = ComplianceExecutionBatch::where('tenant_id', $tenantId)
                    ->where('id', $batchId)
                    ->firstOrFail();

                $month = $batch->period_month;
                $year = $batch->period_year;
                $branchId = $batch->branch_id ?? $branchId;
            }

            // Resolve branch ID safely
            if (!$branchId) {
                $branchId = \App\Models\Branch::where('tenant_id', $tenantId)->first()?->id;
            }

            // Execute through orchestrator
            $result = $this->orchestrator->execute(
                $tenantId,
                $branchId,
                $month,
                $year,
                $formCode,
                'preview',
                $batchId
            );

            if ($result['status'] === 'failed') {
                abort(400, $result['error']);
            }

            Log::info('Compliance Preview', [
                'form'      => $formCode,
                'batch_id'  => $batchId,
                'rows'      => $result['result']['rows_count'] ?? 0,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
            ]);

            // Return the HTML already rendered by the orchestrator
            return response($result['result']['html'])->header('Content-Type', 'text/html');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Batch or form not found');
        } catch (\Exception $e) {
            Log::error('Preview Error', [
                'form' => $formCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Preview failed: ' . $e->getMessage());
        }
    }
}
