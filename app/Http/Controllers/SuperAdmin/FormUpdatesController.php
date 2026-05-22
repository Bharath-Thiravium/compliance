<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceFormsMaster;

class FormUpdatesController extends Controller
{
    public function index()
    {
        $allForms = ComplianceFormsMaster::with('section')
            ->orderBy('section_id')
            ->orderBy('form_code')
            ->get();

        $recentUpdates = ComplianceFormsMaster::with('section')
            ->where('updated_at', '>=', now()->subDays(30))
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $stats = [
            'total_forms'    => ComplianceFormsMaster::count(),
            'active_forms'   => ComplianceFormsMaster::where('is_active', true)->count(),
            'inactive_forms' => ComplianceFormsMaster::where('is_active', false)->count(),
            'recent_updates' => ComplianceFormsMaster::where('updated_at', '>=', now()->subDays(30))->count(),
            'with_changes'   => ComplianceFormsMaster::whereNotNull('change_summary')->count(),
        ];

        return view('super-admin.form-updates', compact('allForms', 'recentUpdates', 'stats'));
    }
}
