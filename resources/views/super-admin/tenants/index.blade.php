@extends('super-admin.layout')
@section('title', 'Tenants')
@section('page-title', 'Tenants')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span class="sa-card-title">All Tenants ({{ $tenants->total() }})</span>
        <a href="{{ route('super-admin.tenants.create') }}" class="sa-btn sa-btn-accent">+ New Tenant</a>
    </div>
    <div class="sa-card-body" style="padding:16px 20px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
            <input type="text" name="search" class="sa-input" style="max-width:240px;" placeholder="Search company..." value="{{ request('search') }}">
            <select name="subscription" class="sa-select" style="max-width:160px;">
                <option value="">All Plans</option>
                <option value="FULL"    {{ request('subscription') === 'FULL'    ? 'selected' : '' }}>FULL</option>
                <option value="MINIMAL" {{ request('subscription') === 'MINIMAL' ? 'selected' : '' }}>MINIMAL</option>
            </select>
            <button type="submit" class="sa-btn sa-btn-primary">Filter</button>
            <a href="{{ route('super-admin.tenants') }}" class="sa-btn sa-btn-outline">Reset</a>
        </form>
    </div>
    <div style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Company Name</th>
                    <th>Subscription</th>
                    <th>Users</th>
                    <th>Admin Email</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td style="color:#aaa;">{{ $tenant->id }}</td>
                    <td><strong>{{ $tenant->name }}</strong></td>
                    <td>
                        <span class="badge-{{ strtolower($tenant->subscription_type) }}">
                            {{ $tenant->subscription_type }}
                        </span>
                    </td>
                    <td>{{ $tenant->users_count }}</td>
                    <td style="font-size:12px;color:#555;">{{ $tenant->users->first()?->email ?? '—' }}</td>
                    <td>{{ $tenant->created_at->format('d M Y') }}</td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="sa-btn sa-btn-outline sa-btn-sm">✏️ Edit</a>

                        <form method="POST" action="{{ route('super-admin.tenants.toggle', $tenant) }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm" title="Toggle subscription">
                                🔄 {{ $tenant->subscription_type === 'FULL' ? '→ MINIMAL' : '→ FULL' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('super-admin.tenants.delete', $tenant) }}" style="margin:0;"
                              onsubmit="return confirm('Delete tenant {{ addslashes($tenant->name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#aaa;padding:32px;">No tenants found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
    <div style="padding:16px 20px;">{{ $tenants->links() }}</div>
    @endif
</div>
@endsection
