@extends('super-admin.layout')
@section('title', 'Tenants')
@section('page-title', 'Tenants')

@section('content')
<div class="card">
    <div class="card-header flex-between">
        <span>All Tenants ({{ $tenants->total() }})</span>
        <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-accent btn-sm">+ New Tenant</a>
    </div>
    <div class="card-body">
        <form method="GET" class="flex-wrap-gap mb-3">
            <input type="text" name="search" class="form-input" style="max-width:240px;" placeholder="Search company..." value="{{ request('search') }}">
            <select name="subscription" class="form-select" style="max-width:160px;">
                <option value="">All Plans</option>
                <option value="FULL"    {{ request('subscription') === 'FULL'    ? 'selected' : '' }}>FULL</option>
                <option value="MINIMAL" {{ request('subscription') === 'MINIMAL' ? 'selected' : '' }}>MINIMAL</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('super-admin.tenants') }}" class="btn btn-sm">Reset</a>
        </form>
    </div>
    <div class="mobile-table-wrap">
        <table class="data-table">
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
                    <td class="text-muted">{{ $tenant->id }}</td>
                    <td><strong>{{ $tenant->name }}</strong></td>
                    <td>
                        <span class="badge badge-{{ strtolower($tenant->subscription_type) }}">
                            {{ $tenant->subscription_type }}
                        </span>
                    </td>
                    <td>{{ $tenant->users_count }}</td>
                    <td class="text-xs text-muted">{{ $tenant->users->first()?->email ?? '—' }}</td>
                    <td>{{ $tenant->created_at->format('d M Y') }}</td>
                    <td class="flex-wrap-gap">
                        <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-sm">✏️ Edit</a>

                        <form method="POST" action="{{ route('super-admin.tenants.toggle', $tenant) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm" title="Toggle subscription">
                                🔄 {{ $tenant->subscription_type === 'FULL' ? '→ MINIMAL' : '→ FULL' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('super-admin.tenants.delete', $tenant) }}"
                              onsubmit="return confirm('Delete tenant {{ addslashes($tenant->name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:#aaa; padding:32px;">No tenants found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
    <div class="p-3">{{ $tenants->links() }}</div>
    @endif
</div>
@endsection
