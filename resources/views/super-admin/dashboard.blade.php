@extends('super-admin.layout')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="sa-stat d-flex justify-content-between align-items-center">
            <div>
                <div class="sa-stat-value">{{ $stats['total_tenants'] }}</div>
                <div class="sa-stat-label">Total Tenants</div>
            </div>
            <div class="sa-stat-icon">🏢</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="sa-stat d-flex justify-content-between align-items-center">
            <div>
                <div class="sa-stat-value">{{ $stats['total_users'] }}</div>
                <div class="sa-stat-label">Total Users</div>
            </div>
            <div class="sa-stat-icon">👥</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="sa-stat d-flex justify-content-between align-items-center">
            <div>
                <div class="sa-stat-value" style="color:#52c41a;">{{ $stats['full_tenants'] }}</div>
                <div class="sa-stat-label">Full Subscription</div>
            </div>
            <div class="sa-stat-icon">✅</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="sa-stat d-flex justify-content-between align-items-center">
            <div>
                <div class="sa-stat-value" style="color:#fa8c16;">{{ $stats['minimal_tenants'] }}</div>
                <div class="sa-stat-label">Minimal Subscription</div>
            </div>
            <div class="sa-stat-icon">⚡</div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span class="sa-card-title">Recent Tenants</span>
        <a href="{{ route('super-admin.tenants.create') }}" class="sa-btn sa-btn-accent sa-btn-sm">+ New Tenant</a>
    </div>
    <div class="sa-card-body" style="padding:0;">
        <table class="sa-table">
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
                        <span class="badge-{{ strtolower($tenant->subscription_type) }}">
                            {{ $tenant->subscription_type }}
                        </span>
                    </td>
                    <td>{{ $tenant->users_count }}</td>
                    <td>{{ $tenant->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="sa-btn sa-btn-outline sa-btn-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#aaa;padding:32px;">No tenants yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="{{ route('super-admin.tenants') }}" class="sa-btn sa-btn-primary">🏢 Manage Tenants</a>
    <a href="{{ route('super-admin.users') }}"   class="sa-btn sa-btn-outline">👥 Manage Users</a>
    <a href="{{ route('super-admin.users.create') }}" class="sa-btn sa-btn-accent">➕ Create New User</a>
</div>
@endsection
