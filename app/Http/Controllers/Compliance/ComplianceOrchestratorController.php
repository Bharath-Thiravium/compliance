<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceOrchestratorController extends Controller
{
    public function __construct(
        private ComplianceOrchestrator $orchestrator
    ) {}

    /**
     * Show orchestrator dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $forms = ComplianceFormsMaster::where('is_active', true)
            ->orderBy('form_code')
            ->get();

        $branches = \App\Models\Branch::where('tenant_id', $tenantId)->get();

        $recentLogs = \Illuminate\Support\Facades\DB::table('compliance_execution_logs')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('compliance.orchestrator.dashboard', compact('forms', 'branches', 'recentLogs'));
    }

    /**
     * Run orchestrator execution
     */
    public function run(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $validated = $request->validate([
            'form_code' => 'required|string|exists:compliance_forms_master,form_code',
            'branch_id' => 'required|integer|exists:branches,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'mode' => 'required|in:preview,pdf,batch',
            'batch_id' => 'nullable|integer|exists:compliance_execution_batches,id'
        ]);

        // Verify branch belongs to tenant
        $branch = \App\Models\Branch::where('id', $validated['branch_id'])
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Execute orchestrator
        $result = $this->orchestrator->execute(
            $tenantId,
            $validated['branch_id'],
            $validated['month'],
            $validated['year'],
            $validated['form_code'],
            $validated['mode'],
            $validated['batch_id'] ?? null
        );

        if ($result['status'] === 'failed') {
            return response()->json($result, 400);
        }

        // Handle different modes
        if ($validated['mode'] === 'preview') {
            return response()->json([
                'status' => 'success',
                'html' => $result['result']['html'],
                'is_nil' => $result['result']['is_nil'],
                'rows_count' => $result['result']['rows_count']
            ]);
        }

        if ($validated['mode'] === 'pdf') {
            return response($result['result']['content'], 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$validated['form_code']}.pdf\"");
        }

        // Batch mode
        return response()->json([
            'status' => 'success',
            'file_path' => $result['result']['file_path'],
            'file_size' => $result['result']['file_size'],
            'execution_time' => $result['execution_time'],
            'records_generated' => $result['records_generated']
        ]);
    }

    /**
     * Get execution logs
     */
    public function logs(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $validated = $request->validate([
            'batch_id' => 'required|integer|exists:compliance_execution_batches,id',
            'form_code' => 'nullable|string'
        ]);

        // Verify batch belongs to tenant
        $batch = ComplianceExecutionBatch::where('id', $validated['batch_id'])
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $logs = $this->orchestrator->getExecutionLogs(
            $validated['batch_id'],
            $validated['form_code'] ?? null
        );

        $stats = $this->orchestrator->getExecutionStats($validated['batch_id']);

        return response()->json([
            'status' => 'success',
            'logs' => $logs,
            'statistics' => $stats
        ]);
    }

    /**
     * Get execution statistics
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $validated = $request->validate([
            'batch_id' => 'required|integer|exists:compliance_execution_batches,id'
        ]);

        // Verify batch belongs to tenant
        $batch = ComplianceExecutionBatch::where('id', $validated['batch_id'])
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $stats = $this->orchestrator->getExecutionStats($validated['batch_id']);

        return response()->json([
            'status' => 'success',
            'statistics' => $stats
        ]);
    }
}
