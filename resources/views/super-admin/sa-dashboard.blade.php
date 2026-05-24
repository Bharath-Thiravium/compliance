@extends('super-admin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
    <div class="ant-card">
        <div class="ant-card-head">📊 System Overview</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3>{{ $stats['total_users'] }}</h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3>{{ $stats['total_tenants'] }}</h3>
                        <p>Total Tenants</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3>{{ $stats['total_batches'] }}</h3>
                        <p>Total Batches</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3>{{ $stats['total_audits'] }}</h3>
                        <p>Total Audits</p>
                    </div>
                </div>
            </div>
            <div class="ant-row mt-4">
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3 style="color: #52c41a;">{{ $stats['successful_actions'] }}</h3>
                        <p>Successful Actions</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $stats['failed_actions'] }}</h3>
                        <p>Failed Actions</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3 style="color: #13c2c2;">{{ $stats['total_downloads'] }}</h3>
                        <p>Total Downloads</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3 style="color: #faad14;">{{ $stats['total_previews'] }}</h3>
                        <p>Total Previews</p>
                    </div>
                </div>
            </div>
            <div class="ant-row mt-4">
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3 style="color: #52c41a;">{{ $stats['active_users'] }}</h3>
                        <p>Active Users</p>
                    </div>
                </div>
                <div class="ant-col ant-col-3">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $stats['inactive_users'] }}</h3>
                        <p>Inactive Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ant-card section-spacing">
        <div class="ant-card-head warning">⚠️ Alerts & Warnings</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $alerts['audit_failures'] }}</h3>
                        <p>Audit Failures</p>
                    </div>
                </div>
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $alerts['generation_failures'] }}</h3>
                        <p>Generation Failures</p>
                    </div>
                </div>
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #faad14;">{{ $alerts['inactive_forms'] }}</h3>
                        <p>Inactive Forms</p>
                    </div>
                </div>
            </div>
            <div class="ant-row mt-4">
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #faad14;">{{ $alerts['pending_batches'] }}</h3>
                        <p>Pending Batches</p>
                    </div>
                </div>
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #faad14;">{{ $pendingFilingsCount }}</h3>
                        <p>Users Pending Filing</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ant-card section-spacing">
        <div class="ant-card-head info">📋 Government Form Updates</div>
        <div class="ant-card-body">
            <div class="mb-4">
                <h4 style="margin: 0 0 16px 0;">Recent Updates (Last 30 Days): <strong>{{ $formUpdates['total_updates'] }}</strong></h4>
            </div>
            @if($formUpdates['recent_updates']->count() > 0)
                <div style="overflow-x: auto;" class="mobile-table-wrap">
                    <table class="ant-table">
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
                                        <span class="ant-tag {{ $form->is_active ? 'ant-tag-success' : 'ant-tag-error' }}">
                                            {{ $form->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $form->updated_at ? $form->updated_at->diffForHumans() : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="{{ route('super-admin.form-updates') }}" class="ant-btn ant-btn-primary">View All Updates</a>
                </div>
            @else
                <p class="text-muted text-center">No recent form updates.</p>
            @endif
        </div>
    </div>

    <div class="ant-row">
        <div class="ant-col ant-col-4">
            <a href="{{ route('super-admin.audit-details') }}" class="ant-btn ant-btn-primary w-100" style="height: 60px; font-size: 16px;">
                🔍 View Audit Details
            </a>
        </div>
        <div class="ant-col ant-col-4">
            <a href="{{ route('super-admin.audit-failures') }}" class="ant-btn ant-btn-warning w-100" style="height: 60px; font-size: 16px; background: #ff4d4f; border-color: #ff4d4f; color: white;">
                ⚠️ Audit Failed Details
            </a>
        </div>
        <div class="ant-col ant-col-4">
            <a href="{{ route('super-admin.form-updates') }}" class="ant-btn ant-btn-info w-100" style="height: 60px; font-size: 16px;">
                📋 Government Form Updates
            </a>
        </div>
    </div>
    <div class="ant-row mt-4">
        <div class="ant-col ant-col-6">
            <a href="{{ route('super-admin.pending-filings') }}" class="ant-btn ant-btn-warning w-100" style="height: 60px; font-size: 16px; background: #faad14; border-color: #faad14; color: white;">
                👤 Users Pending Filing
            </a>
        </div>
        <div class="ant-col ant-col-6">
            <a href="{{ route('compliance.dashboard') }}" class="ant-btn ant-btn-info w-100" style="height: 60px; font-size: 16px;">
                🏭 Go to Compliance Dashboard
            </a>
        </div>
    </div>

    <div class="ant-card section-spacing">
        <div class="ant-card-head">📦 Recent Batch Activity</div>
        <div class="ant-card-body">
            @if($recentBatches->count() > 0)
                <div style="overflow-x: auto;" class="mobile-table-wrap">
                    <table class="ant-table">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Tenant</th>
                                <th>Section</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBatches as $batch)
                                <tr>
                                    <td><strong>#{{ $batch->id }}</strong></td>
                                    <td>{{ optional($batch->tenant)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($batch->section)->section_name ?? 'N/A' }}</td>
                                    <td>
                                        @if(!empty($batch->period_month) && !empty($batch->period_year))
                                            {{ \Carbon\Carbon::createFromDate($batch->period_year, $batch->period_month, 1)->format('M Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td><span class="ant-tag ant-tag-success">{{ $batch->status ?? 'N/A' }}</span></td>
                                    <td>{{ $batch->created_at ? $batch->created_at->diffForHumans() : 'N/A' }}</td>
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

    <div class="ant-card section-spacing">
        <div class="ant-card-head success">📥 Recent Downloads</div>
        <div class="ant-card-body">
            @if($recentDownloads->count() > 0)
                <div style="overflow-x: auto;" class="mobile-table-wrap">
                    <table class="ant-table">
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
                                    <td><span class="ant-tag ant-tag-info">{{ optional($log->user)->role ?? 'N/A' }}</span></td>
                                    <td><strong>#{{ $log->batch_id ?? 'N/A' }}</strong></td>
                                    <td><span class="ant-tag ant-tag-success">{{ $log->action_label ?? $log->action_type }}</span></td>
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

    <div class="ant-card section-spacing">
        <div class="ant-card-head info">🔍 Recent Audit Activity</div>
        <div class="ant-card-body">
            @if($recentAudits->count() > 0)
                <div style="overflow-x: auto;" class="mobile-table-wrap">
                    <table class="ant-table">
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
                                    <td><span class="ant-tag ant-tag-info">{{ optional($log->user)->role ?? 'N/A' }}</span></td>
                                    <td>{{ $log->action_label ?? $log->action_type }}</td>
                                    <td><strong>#{{ $log->batch_id ?? '-' }}</strong></td>
                                    <td>{{ $log->form_code ?? '-' }}</td>
                                    <td>
                                        <span class="ant-tag {{ $log->status === 'success' ? 'ant-tag-success' : 'ant-tag-error' }}">
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

    <div class="ant-card section-spacing">
        <div class="ant-card-head warning">👤 Inactive Users</div>
        <div class="ant-card-body">
            @if($inactiveUsers->count() > 0)
                <div style="overflow-x: auto;" class="mobile-table-wrap">
                    <table class="ant-table">
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
                                    <td><span class="ant-tag ant-tag-info">{{ $user->role ?? 'N/A' }}</span></td>
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
