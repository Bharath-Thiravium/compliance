@extends('super-admin.layout')

@section('title', 'Pending Filings - Super Admin')
@section('page-title', 'Pending Filings')

@section('content')
    <div class="page-header">
        <a href="{{ route('super-admin.dashboard') }}" class="btn">← Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-header warning">📊 Pending Filings Summary</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-2">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $stats['pending_batches'] ?? 0 }}</h3>
                        <p>Pending Batches</p>
                    </div>
                </div>
                <div class="grid-col col-1-2">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $stats['users_with_pending'] ?? 0 }}</h3>
                        <p>Users With Pending Filings</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header warning">📦 Pending Batches</div>
        <div class="card-body">
            @if($pendingBatches->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
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
                            @foreach($pendingBatches as $batch)
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
                                        <span class="badge badge-warning">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td>{{ $batch->created_at ? $batch->created_at->diffForHumans() : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sa-pagination">
                    {{ $pendingBatches->withQueryString()->links() }}
                </div>
            @else
                <p class="text-muted text-center">No pending batches found.</p>
            @endif
        </div>
    </div>

    <div class="card section-spacing">
        <div class="card-header warning">👤 Users With Pending Filings</div>
        <div class="card-body">
            @if($usersWithPending->count() > 0)
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
                            @foreach($usersWithPending as $user)
                                <tr>
                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                    <td>{{ $user->email ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $user->role ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($user->tenant)->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}
                                    </td>
                                    <td><span class="badge badge-warning">Pending Filing</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sa-pagination">
                    {{ $usersWithPending->withQueryString()->links() }}
                </div>
            @else
                <p class="text-muted text-center">No users with pending filings found.</p>
            @endif
        </div>
    </div>
@endsection
