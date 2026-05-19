<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use App\Services\ManualComplianceLoader;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ManualComplianceController extends Controller
{
    public function __construct(private ManualComplianceLoader $loader) {}

    public function createBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
        ]);

        $batch = ComplianceExecutionBatch::create([
            'tenant_id' => $validated['tenant_id'],
            'branch_id' => $validated['branch_id'],
            'period_month' => $validated['month'],
            'period_year' => $validated['year'],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $this->loader->load($batch);

        return response()->json([
            'success' => true,
            'batch_id' => $batch->id,
            'message' => 'Batch created and manual compliances loaded',
        ]);
    }
}
