<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ComplianceExecutionBatch;

class PendingFilingsController extends Controller
{
    public function index()
    {
        $pendingBatches = ComplianceExecutionBatch::with(['tenant', 'section'])
            ->whereIn('status', ['pending', 'processing', 'awaiting_data'])
            ->latest('id')
            ->paginate(50);

        $usersWithPending = User::with('tenant')
            ->where('is_super_admin', false)
            ->paginate(50);

        $stats = [
            'pending_batches'   => ComplianceExecutionBatch::whereIn('status', ['pending', 'processing', 'awaiting_data'])->count(),
            'users_with_pending'=> User::where('is_super_admin', false)->count(),
        ];

        return view('super-admin.pending-filings', compact('pendingBatches', 'usersWithPending', 'stats'));
    }
}
