@extends('super-admin.layout')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid-row mb-3">
    <div class="grid-col col-1-4">
        <div class="stat-card">
            <h3>{{ $stats['total_tenants'] }}</h3>
            <p>Total Tenants 🏢</p>
        </div>
    </div>
    <div class="grid-col col-1-4">
        <div class="stat-card">
            <h3>{{ $stats['total_users'] }}</h3>
            <p>Total Users 👥</p>
        </div>
    </div>
    <div class="grid-col col-1-4">
        <div class="stat-card">
            <h3 style="color:var(--color-success);">{{ $stats['full_tenants'] }}</h3>
            <p>Full Subscription ✅</p>
        </div>
    </div>
    <div class="grid-col col-1-4">
        <div class="stat-card">
            <h3 style="color:var(--color-warning);">{{ $stats['minimal_tenants'] }}</h3>
            <p>Minimal Subscription ⚡</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header flex-between">
        <span>Recent Tenants</span>
        <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-accent btn-sm">+ New Tenant</a>
    </div>
    <div class="mobile-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Subscription</th>
                    <th>Users</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_tenants as $tenant)
                <tr>
                    <td><strong>{{ $tenant->name }}</strong></td>
                    <td>
                        <span class="badge badge-{{ strtolower($tenant->subscription_type) }}">
                            {{ $tenant->subscription_type }}
                        </span>
                    </td>
                    <td>{{ $tenant->users_count }}</td>
                    <td>{{ $tenant->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted" style="padding:32px;">No tenants yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="flex-wrap-gap mt-3">
    <a href="{{ route('super-admin.tenants') }}" class="btn btn-primary">🏢 Manage Tenants</a>
    <a href="{{ route('super-admin.users') }}" class="btn">👥 Manage Users</a>
    <a href="{{ route('super-admin.users.create') }}" class="btn btn-accent">➕ Create New User</a>
</div>
@endsection
