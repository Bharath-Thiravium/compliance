@extends('super-admin.layout')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="card">
    <div class="card-header flex-between">
        <span>All Users ({{ $users->total() }})</span>
        <a href="{{ route('super-admin.users.create') }}" class="btn btn-accent btn-sm">+ New User</a>
    </div>
    <div class="card-body">
        <form method="GET" class="flex-wrap-gap mb-3">
            <input type="text" name="search" class="form-input" style="max-width:240px;" placeholder="Name or email..." value="{{ request('search') }}">
            <select name="tenant_id" class="form-select" style="max-width:200px;">
                <option value="">All Tenants</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('super-admin.users') }}" class="btn btn-sm">Reset</a>
        </form>
    </div>
    <div class="mobile-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Tenant</th>
                    <th>Subscription</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted">{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td>{{ $user->tenant?->name ?? '—' }}</td>
                    <td>
                        @if($user->tenant)
                            <span class="badge badge-{{ strtolower($user->tenant->subscription_type) }}">
                                {{ $user->tenant->subscription_type }}
                            </span>
                        @else
                            <span style="color:#aaa;">—</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td class="flex-wrap-gap">
                        <a href="{{ route('super-admin.users.edit', $user) }}" class="btn btn-sm">✏️ Edit</a>
                        <form method="POST" action="{{ route('super-admin.users.delete', $user) }}"
                              onsubmit="return confirm('Delete user {{ addslashes($user->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:#aaa; padding:32px;">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-3">{{ $users->links() }}</div>
    @endif
</div>
@endsection
