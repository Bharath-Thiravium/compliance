<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditFailuresController extends Controller
{
    public function index(Request $request)
    {
        $perPage     = 50;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $groupedFailures = ComplianceAuditLog::where('audit_score', '<', 100)
            ->with(['batch.section', 'batch.creator'])
            ->latest()
            ->get()
            ->filter(fn($log) => $log->batch && $log->batch->creator)
            ->groupBy(fn($log) => $log->batch->creator_id)
            ->map(function ($auditLogs) {
                $firstLog = $auditLogs->first();
                $batch    = $firstLog->batch;
                $user     = optional($batch)->creator;
                $section  = optional($batch)->section;

                return [
                    'user_id'    => $user->id ?? null,
                    'user_name'  => $user->name ?? 'N/A',
                    'user_email' => $user->email ?? 'N/A',
                    'audit_score'=> $firstLog->audit_score ?? 'N/A',
                    'section_name'=> $section->section_name ?? 'N/A',
                    'created_at' => $firstLog->created_at,
                    'issues'     => $auditLogs->map(fn($log) => [
                        'form_code'   => $log->form_code ?? 'N/A',
                        'audit_score' => $log->audit_score ?? 'N/A',
                        'violations'  => $log->violations ?? [],
                        'batch_id'    => $log->batch_id ?? 'N/A',
                        'created_at'  => $log->created_at,
                    ])->values()->toArray(),
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        $userFailures = new LengthAwarePaginator(
            $groupedFailures->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $groupedFailures->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'users_with_low_score'   => ComplianceAuditLog::where('audit_score', '<', 100)->with('batch')->get()->filter(fn($l) => $l->batch && $l->batch->creator_id)->pluck('batch.creator_id')->unique()->count(),
            'total_low_score_records'=> ComplianceAuditLog::where('audit_score', '<', 100)->count(),
        ];

        return view('super-admin.audit-failures', compact('userFailures', 'stats'));
    }
}
