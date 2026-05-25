@extends('super-admin.layout')

@section('title', 'All User Batches')
@section('page-title', 'All User Batches')

@section('content')
    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">🔍 Filter Batches</div>
        <div class="card-body">
            <form method="GET" action="{{ route('super-admin.batches.index') }}" class="grid-row" style="gap:12px;align-items:flex-end;">
                <div class="grid-col col-1-4">
                    <label>Tenant</label>
                    <select name="tenant_id" class="form-control">
                        <option value="">All Tenants</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid-col col-1-4">
                    <label>User</label>
                    <select name="user_id" class="form-control">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid-col col-1-4">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach(['pending','processing','completed','partial','failed'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid-col col-1-4">
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Batches Table --}}
    <div class="card">
        <div class="card-header">📦 Batches ({{ $batches->total() }} total)</div>
        <div class="card-body">
            @if($batches->count())
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Batch #</th>
                                <th>User</th>
                                <th>Tenant</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Forms</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                                <tr>
                                    <td>
                                        <strong>#{{ $batch->user_batch_number ?? $batch->id }}</strong>
                                        <br><small class="text-muted">ID: {{ $batch->id }}</small>
                                    </td>
                                    <td>
                                        {{ optional($batch->creator)->name ?? '—' }}
                                        <br><small class="text-muted">{{ optional($batch->creator)->email ?? '' }}</small>
                                    </td>
                                    <td>{{ optional($batch->tenant)->name ?? '—' }}</td>
                                    <td>
                                        @if($batch->period_month && $batch->period_year)
                                            {{ \Carbon\Carbon::createFromDate($batch->period_year, $batch->period_month, 1)->format('M Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badge = match($batch->status) {
                                                'completed'  => 'badge-success',
                                                'partial'    => 'badge-warning',
                                                'failed'     => 'badge-danger',
                                                'processing' => 'badge-info',
                                                default      => 'badge-default',
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ ucfirst($batch->status) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">{{ $batch->form_generated }}</span> /
                                        <span class="badge badge-default">{{ $batch->form_total }}</span>
                                        @if($batch->form_failed > 0)
                                            <span class="badge badge-danger">{{ $batch->form_failed }} failed</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $batch->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <a href="{{ route('super-admin.batches.show', $batch->id) }}" class="btn btn-info btn-sm">
                                            🔍 View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $batches->links() }}
                </div>
            @else
                <p class="text-muted text-center py-4">No batches found.</p>
            @endif
        </div>
    </div>
@endsection
