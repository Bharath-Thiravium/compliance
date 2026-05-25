<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;

class BatchesController extends Controller
{
    public function index(Request $request)
    {
        $query = ComplianceExecutionBatch::with(['tenant', 'creator', 'section']);

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('user_id')) {
            $query->where('created_by', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->latest()->paginate(20)->withQueryString();

        // Attach per-batch form counts
        $batchIds = $batches->pluck('id');
        $formCounts = ComplianceBatchForm::whereIn('batch_id', $batchIds)
            ->selectRaw("batch_id, count(*) as total, sum(case when status='generated' then 1 else 0 end) as generated_count, sum(case when status='failed' then 1 else 0 end) as failed_count")
            ->groupBy('batch_id')
            ->get()
            ->keyBy('batch_id');

        foreach ($batches as $batch) {
            $counts = $formCounts->get($batch->id);
            $batch->form_total     = $counts->total          ?? 0;
            $batch->form_generated = $counts->generated_count ?? 0;
            $batch->form_failed    = $counts->failed_count    ?? 0;
        }

        $tenants = Tenant::orderBy('name')->get();
        $users   = User::where('is_super_admin', false)->orderBy('name')->get();

        return view('super-admin.batches.index', compact('batches', 'tenants', 'users'));
    }

    public function show(int $id)
    {
        $batch = ComplianceExecutionBatch::with(['tenant', 'creator', 'section'])->findOrFail($id);
        $forms = ComplianceBatchForm::where('batch_id', $id)->get();

        return view('super-admin.batches.show', compact('batch', 'forms'));
    }
}
