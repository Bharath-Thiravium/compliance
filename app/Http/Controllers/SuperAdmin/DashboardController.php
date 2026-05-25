<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\AuditLog;
use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceGenerationLog;
use App\Models\ComplianceFormsMaster;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'        => User::count(),
            'total_tenants'      => Tenant::count(),
            'total_batches'      => ComplianceExecutionBatch::count(),
            'total_audits'       => AuditLog::count(),
            'successful_actions' => AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'success'")->count(),
            'failed_actions'     => AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'failed'")->count(),
            'total_downloads'    => AuditLog::whereIn('action', ['download_report', 'download_inspection_pack', 'form_downloaded'])->count(),
            'total_previews'     => AuditLog::where('action', 'preview_form')->count(),
            'active_users'       => User::where('is_super_admin', false)->where('is_active', true)->count(),
            'inactive_users'     => User::where('is_super_admin', false)->where('is_active', false)->count(),
        ];

        $alerts = [
            'audit_failures'     => AuditLog::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.status')) = 'failed'")->count(),
            'generation_failures'=> ComplianceGenerationLog::whereNotNull('error_message')->count(),
            'inactive_forms'     => ComplianceFormsMaster::where('is_active', false)->count(),
            'pending_batches'    => ComplianceExecutionBatch::whereIn('status', ['pending', 'processing', 'awaiting_data'])->count(),
            'users_pending_filing' => User::where('is_super_admin', false)
                ->whereIn('tenant_id', ComplianceExecutionBatch::whereIn('status', ['pending', 'processing', 'awaiting_data'])->pluck('tenant_id'))
                ->count(),
        ];

        $formUpdates = [
            'total_updates'  => ComplianceFormsMaster::where('updated_at', '>=', now()->subDays(30))->count(),
            'recent_updates' => ComplianceFormsMaster::with('section')->latest('updated_at')->take(5)->get(),
        ];

        $recentBatches = ComplianceExecutionBatch::with(['tenant', 'section'])->latest()->take(10)->get();

        // Attach audit scores and form details to recent batches
        foreach ($recentBatches as $batch) {
            $auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batch->id)->get();
            $batch->audit_score  = $auditLogs->isNotEmpty() ? round($auditLogs->avg('audit_score')) : null;
            $batch->audit_status = $auditLogs->isNotEmpty()
                ? ($auditLogs->where('status', 'passed')->count() === $auditLogs->count() ? 'passed'
                    : ($auditLogs->where('status', 'passed')->count() === 0 ? 'failed' : 'partial'))
                : null;

            // Attach forms with their individual audit scores
            $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batch->id)->get();
            $batch->batch_forms = $forms->map(function($form) use ($auditLogs) {
                $formAudit = $auditLogs->where('form_code', $form->form_code)->first();
                $form->audit_score  = $formAudit?->audit_score;
                $form->audit_status = $formAudit?->status;
                return $form;
            });
        }
        $recentDownloads = AuditLog::with(['user', 'batch'])->whereIn('action', ['download_report', 'download_inspection_pack', 'form_downloaded'])->latest('created_at')->take(10)->get();
        $recentAudits    = AuditLog::with(['user', 'batch'])->whereIn('action', ['batch_created', 'batch_processed', 'form_previewed', 'form_generated', 'download_inspection_pack', 'download_report'])->latest('created_at')->take(15)->get();

        $inactiveUsers = User::with('tenant')->where('is_super_admin', false)->where('is_active', false)->take(10)->get();

        $pendingFilingsCount = ComplianceExecutionBatch::whereIn('status', ['pending', 'processing', 'awaiting_data'])->count();

        return view('super-admin.sa-dashboard', compact(
            'stats', 'alerts', 'formUpdates', 'recentBatches',
            'recentDownloads', 'recentAudits', 'inactiveUsers', 'pendingFilingsCount'
        ));
    }
}
