@extends('super-admin.layout')

@section('title', 'Audit Details - Super Admin')
@section('page-title', 'Audit Details')

@section('content')
    <div class="card">
        <div class="card-header">📊 Audit Summary</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3>{{ $stats['total_audits'] }}</h3>
                        <p>Total Audit Records</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-success);">{{ $stats['successful'] }}</h3>
                        <p>Successful Actions</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $stats['failed'] }}</h3>
                        <p>Failed Actions</p>
                    </div>
                </div>
            </div>
            <div class="grid-row mt-3">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:#722ed1;">{{ $stats['total_batches'] }}</h3>
                        <p>Total Batches Created</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $stats['total_previews'] }}</h3>
                        <p>Total Forms Previewed</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-info);">{{ $stats['total_downloads'] }}</h3>
                        <p>Total Downloads</p>
                    </div>
                </div>
            </div>
            <div class="grid-row mt-3">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $stats['users_not_filled'] }}</h3>
                        <p>Users Not Filled</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header">🔍 Audit Activity Filters</div>
        <div class="card-body">
            <form method="GET" action="{{ route('super-admin.audit-details') }}">
                <div class="grid-row">
                    <div class="grid-col col-1-3">
                        <div class="form-group">
                    <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid-col col-1-3">
                        <div class="form-group">
                    <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid-col col-1-3">
                        <div class="form-group">
                    <label class="form-label">Action Type</label>
                            <select name="action_type" class="form-select">
                                <option value="">All Actions</option>
                                <option value="login" {{ request('action_type') == 'login' ? 'selected' : '' }}>Login</option>
                                <option value="batch_create" {{ request('action_type') == 'batch_create' ? 'selected' : '' }}>Batch Create</option>
                                <option value="preview_form" {{ request('action_type') == 'preview_form' ? 'selected' : '' }}>Preview Form</option>
                                <option value="download_report" {{ request('action_type') == 'download_report' ? 'selected' : '' }}>Download Report</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="grid-row mt-3">
                    <div class="grid-col col-full">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('super-admin.audit-details') }}" class="btn ms-2">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header">📋 Recent Audit Activity</div>
        <div class="card-body">
            @if($audits->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Tenant</th>
                                <th>Action</th>
                                <th>Form Code</th>
                                <th>Batch ID</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audits as $audit)
                                <tr>
                                    <td>{{ $audit->id }}</td>
                                    <td>{{ optional($audit->user)->name ?? 'System' }}</td>
                                    <td>{{ optional($audit->user)->email ?? '-' }}</td>
                                    <td>{{ optional($audit->tenant)->name ?? '-' }}</td>
                                    <td>{{ $audit->action_label ?? $audit->action_type }}</td>
                                    <td>{{ $audit->form_code ?? '-' }}</td>
                                    <td>{{ $audit->batch_id ? '#' . $audit->batch_id : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $audit->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $audit->status }}
                                        </span>
                                    </td>
                                    <td><small>{{ $audit->ip_address ?? '-' }}</small></td>
                                    <td>{{ $audit->created_at ? $audit->created_at->diffForHumans() : '-' }}</td>
                                    <td>
                                        <a href="{{ route('super-admin.audit-details.show', $audit->id) }}" class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sa-pagination">
                    {{ $audits->links() }}
                </div>
            @else
                <p class="text-muted text-center">No audit records found.</p>
            @endif
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header">📦 Recent Batch Audit</div>
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
                                <th>Created At</th>
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
                                    <td>
                                        @php
                                            $badgeClass = match($batch->status) {
                                                'completed' => 'badge-success',
                                                'partial'   => 'badge-warning',
                                                'failed'    => 'badge-danger',
                                                default     => 'badge-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $batch->status ?? 'N/A' }}</span>
                                    </td>
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

    <div class="card section-spacing">
        <div class="card-header warning">👤 Users Who Have Not Filled Forms</div>
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
                                <th>Status</th>
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
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @elseif($user->last_login_at)
                                            <span class="badge badge-warning">Inactive</span>
                                        @else
                                            <span class="badge badge-danger">Never Logged In</span>
                                        @endif
                                    </td>
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
