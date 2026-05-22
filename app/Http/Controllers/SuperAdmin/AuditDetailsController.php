<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ComplianceExecutionBatch;
use App\Models\User;
use Illuminate\Http\Request;

class AuditDetailsController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'tenant', 'batch']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action_type')) {
            $query->where('action', $request->action_type);
        }

        $audits = $query->latest('created_at')->paginate(50);

        $stats = [
            'total_audits'    => AuditLog::count(),
            'successful'      => AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'success'")->count(),
            'failed'          => AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'failed'")->count(),
            'total_batches'   => ComplianceExecutionBatch::count(),
            'total_previews'  => AuditLog::where('action', 'preview_form')->count(),
            'total_downloads' => AuditLog::whereIn('action', ['download_report', 'download_inspection_pack'])->count(),
            'users_not_filled'=> User::where('is_super_admin', false)->doesntHave('auditLogs')->count(),
        ];

        $recentBatches = ComplianceExecutionBatch::with(['tenant', 'section'])->latest()->take(10)->get();
        $inactiveUsers = User::with('tenant')->where('is_super_admin', false)->doesntHave('auditLogs')->take(10)->get();
        $users         = User::orderBy('name')->get();

        return view('super-admin.audit-details.index', compact(
            'audits', 'stats', 'recentBatches', 'inactiveUsers', 'users'
        ));
    }

    public function show($id)
    {
        $audit = AuditLog::with(['user', 'tenant', 'batch'])->findOrFail($id);

        return view('super-admin.audit-details.show', compact('audit'));
    }
}
