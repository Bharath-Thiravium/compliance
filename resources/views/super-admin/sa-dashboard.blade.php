@extends('super-admin.layout')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">📊 System Overview</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3>{{ $stats['total_users'] }}</h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3>{{ $stats['total_tenants'] }}</h3>
                        <p>Total Tenants</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3>{{ $stats['total_batches'] }}</h3>
                        <p>Total Batches</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3>{{ $stats['total_audits'] }}</h3>
                        <p>Total Audits</p>
                    </div>
                </div>
            </div>
            <div class="grid-row mt-3">
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3 style="color:var(--color-success);">{{ $stats['successful_actions'] }}</h3>
                        <p>Successful Actions</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $stats['failed_actions'] }}</h3>
                        <p>Failed Actions</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3 style="color:var(--color-info);">{{ $stats['total_downloads'] }}</h3>
                        <p>Total Downloads</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $stats['total_previews'] }}</h3>
                        <p>Total Previews</p>
                    </div>
                </div>
            </div>
            <div class="grid-row mt-3">
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3 style="color:var(--color-success);">{{ $stats['active_users'] }}</h3>
                        <p>Active Users</p>
                    </div>
                </div>
                <div class="grid-col col-1-4">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $stats['inactive_users'] }}</h3>
                        <p>Inactive Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header warning">⚠️ Alerts & Warnings</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $alerts['audit_failures'] }}</h3>
                        <p>Audit Failures</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $alerts['generation_failures'] }}</h3>
                        <p>Generation Failures</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $alerts['inactive_forms'] }}</h3>
                        <p>Inactive Forms</p>
                    </div>
                </div>
            </div>
            <div class="grid-row mt-3">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $alerts['pending_batches'] }}</h3>
                        <p>Pending Batches</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $alerts['users_pending_filing'] }}</h3>
                        <p>Users Pending Filing</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header info">📋 Government Form Updates</div>
        <div class="card-body">
            <div class="mb-3">
                <h4 class="mb-3">Recent Updates (Last 30 Days): <strong>{{ $formUpdates['total_updates'] }}</strong></h4>
            </div>
            @if($formUpdates['recent_updates']->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Form Code</th>
                                <th>Form Name</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formUpdates['recent_updates'] as $form)
                                <tr>
                                    <td><strong>{{ $form->form_code }}</strong></td>
                                    <td>{{ $form->form_name }}</td>
                                    <td>{{ optional($form->section)->section_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $form->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $form->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $form->updated_at ? $form->updated_at->diffForHumans() : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="{{ route('super-admin.form-updates') }}" class="btn btn-primary">View All Updates</a>
                </div>
            @else
                <p class="text-muted text-center">No recent form updates.</p>
            @endif
        </div>
    </div>

    <div class="grid-row section-spacing">
        <div class="grid-col col-1-3">
            <a href="{{ route('super-admin.audit-details') }}" class="btn btn-primary w-100 btn-lg">
                🔍 View Audit Details
            </a>
        </div>
        <div class="grid-col col-1-3">
            <a href="{{ route('super-admin.audit-failures') }}" class="btn btn-danger w-100 btn-lg">
                ⚠️ Audit Failed Details
            </a>
        </div>
        <div class="grid-col col-1-3">
            <a href="{{ route('super-admin.form-updates') }}" class="btn btn-info w-100 btn-lg">
                📋 Government Form Updates
            </a>
        </div>
    </div>
    <div class="grid-row mt-3">
        <div class="grid-col col-1-2">
            <a href="{{ route('super-admin.pending-filings') }}" class="btn btn-warning w-100 btn-lg">
                👤 Users Pending Filing
            </a>
        </div>
        <div class="grid-col col-1-2">
            <a href="{{ route('compliance.dashboard') }}" class="btn btn-info w-100 btn-lg">
                🏭 Go to Compliance Dashboard
            </a>
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header">📦 Recent Batch Activity</div>
        <div class="card-body">
            @if($recentBatches->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Tenant</th>
                                <th>Section</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Audit Score</th>
                                <th>Audit Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBatches as $batch)
                                <tr class="batch-row" style="cursor:pointer;" onclick="toggleBatchForms({{ $batch->id }})">
                                    <td><strong>#{{ $batch->id }}</strong> <span style="font-size:11px;color:#aaa;">▼</span></td>
                                    <td>{{ optional($batch->tenant)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($batch->section)->section_name ?? 'N/A' }}</td>
                                    <td>
                                        @if(!empty($batch->period_month) && !empty($batch->period_year))
                                            {{ \Carbon\Carbon::createFromDate($batch->period_year, $batch->period_month, 1)->format('M Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $batchBadge = match($batch->status) {
                                                'completed' => 'badge-success',
                                                'partial'   => 'badge-warning',
                                                'failed'    => 'badge-danger',
                                                'abandoned' => 'badge-danger',
                                                default     => 'badge-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $batchBadge }}">{{ $batch->status ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($batch->audit_score !== null)
                                            <strong>{{ $batch->audit_score }}%</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($batch->audit_status)
                                            @php
                                                $auditBadge = match($batch->audit_status) {
                                                    'passed'  => 'badge-success',
                                                    'partial' => 'badge-warning',
                                                    'failed'  => 'badge-danger',
                                                    default   => 'badge-info',
                                                };
                                            @endphp
                                            <span class="badge {{ $auditBadge }}">{{ ucfirst($batch->audit_status) }}</span>
                                        @else
                                            <span class="text-muted">Not Audited</span>
                                        @endif
                                    </td>
                                    <td>{{ $batch->created_at ? $batch->created_at->diffForHumans() : 'N/A' }}</td>
                                </tr>
                                {{-- Expandable forms row --}}
                                <tr id="batch-forms-{{ $batch->id }}" style="display:none; background:#f9f9f9;">
                                    <td colspan="8" style="padding:0;">
                                        <table class="data-table" style="margin:0; font-size:12px;">
                                            <thead>
                                                <tr style="background:#f0f0f0;">
                                                    <th style="padding:6px 12px;">Form Code</th>
                                                    <th>Section</th>
                                                    <th>Status</th>
                                                    <th>Audit Score</th>
                                                    <th>Audit Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($batch->batch_forms as $form)
                                                    <tr>
                                                        <td style="padding:5px 12px;"><strong>{{ $form->form_code }}</strong></td>
                                                        <td>{{ $form->section ?? '-' }}</td>
                                                        <td>
                                                            @php
                                                                $fBadge = match($form->status) {
                                                                    'generated'  => 'badge-success',
                                                                    'failed'     => 'badge-danger',
                                                                    'processing' => 'badge-info',
                                                                    default      => 'badge-default',
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $fBadge }}">{{ ucfirst($form->status) }}</span>
                                                        </td>
                                                        <td>
                                                            @if($form->audit_score !== null)
                                                                <strong>{{ $form->audit_score }}%</strong>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($form->audit_status)
                                                                @php
                                                                    $fAuditBadge = match($form->audit_status) {
                                                                        'passed'  => 'badge-success',
                                                                        'failed'  => 'badge-danger',
                                                                        default   => 'badge-warning',
                                                                    };
                                                                @endphp
                                                                <span class="badge {{ $fAuditBadge }}">{{ ucfirst($form->audit_status) }}</span>
                                                            @else
                                                                <span class="text-muted">Not Audited</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-muted text-center" style="padding:8px;">No forms found.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No batch activity yet.</p>
            @endif
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header success">📥 Recent Downloads</div>
        <div class="card-body">
            @if($recentDownloads->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Batch ID</th>
                                <th>Action</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentDownloads as $log)
                                <tr>
                                    <td>{{ optional($log->user)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($log->user)->email ?? 'N/A' }}</td>
                                    <td><span class="badge badge-info">{{ optional($log->user)->role ?? 'N/A' }}</span></td>
                                    <td><strong>#{{ $log->batch_id ?? 'N/A' }}</strong></td>
                                    <td><span class="badge badge-success">{{ $log->action_label ?? $log->action_type }}</span></td>
                                    <td>{{ $log->created_at ? $log->created_at->diffForHumans() : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No download activity yet.</p>
            @endif
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header info">🔍 Recent Audit Activity</div>
        <div class="card-body">
            @if($recentAudits->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Batch ID</th>
                                <th>Form Code</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAudits as $log)
                                <tr>
                                    <td>{{ optional($log->user)->name ?? 'System' }}</td>
                                    <td>{{ optional($log->user)->email ?? 'N/A' }}</td>
                                    <td><span class="badge badge-info">{{ optional($log->user)->role ?? 'N/A' }}</span></td>
                                    <td>{{ $log->action_label ?? $log->action_type }}</td>
                                    <td><strong>#{{ $log->batch_id ?? '-' }}</strong></td>
                                    <td>{{ $log->form_code ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $log->status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $log->created_at ? $log->created_at->diffForHumans() : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No audit logs yet.</p>
            @endif
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header warning">👤 Inactive Users</div>
        <div class="card-body">
            @if($inactiveUsers->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tenant</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inactiveUsers as $user)
                                <tr>
                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                    <td>{{ $user->email ?? 'N/A' }}</td>
                                    <td><span class="badge badge-info">{{ $user->role ?? 'N/A' }}</span></td>
                                    <td>{{ optional($user->tenant)->name ?? 'N/A' }}</td>
                                    <td>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">All users are active.</p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
function toggleBatchForms(batchId) {
    const row = document.getElementById('batch-forms-' + batchId);
    if (!row) return;
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endpush
