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

        // Only users who have at least one pending/processing batch
        $tenantIdsWithPending = ComplianceExecutionBatch::whereIn('status', ['pending', 'processing', 'awaiting_data'])
            ->pluck('tenant_id')
            ->unique();

        $usersWithPending = User::with('tenant')
            ->where('is_super_admin', false)
            ->whereIn('tenant_id', $tenantIdsWithPending)
            ->paginate(50);

        $stats = [
            'pending_batches'    => $pendingBatches->total(),
            'users_with_pending' => $usersWithPending->total(),
        ];

        return view('super-admin.pending-filings', compact('pendingBatches', 'usersWithPending', 'stats'));
    }
}
