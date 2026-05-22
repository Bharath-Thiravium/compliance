@extends('super-admin.layout')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span class="sa-card-title">All Users ({{ $users->total() }})</span>
        <a href="{{ route('super-admin.users.create') }}" class="sa-btn sa-btn-accent">+ New User</a>
    </div>
    <div class="sa-card-body" style="padding:16px 20px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
            <input type="text" name="search" class="sa-input" style="max-width:240px;" placeholder="Name or email..." value="{{ request('search') }}">
            <select name="tenant_id" class="sa-select" style="max-width:200px;">
                <option value="">All Tenants</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="sa-btn sa-btn-primary">Filter</button>
            <a href="{{ route('super-admin.users') }}" class="sa-btn sa-btn-outline">Reset</a>
        </form>
    </div>
    <div style="overflow-x:auto;">
        <table class="sa-table">
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
                    <td style="color:#aaa;">{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td style="color:#555;">{{ $user->email }}</td>
                    <td>{{ $user->tenant?->name ?? '—' }}</td>
                    <td>
                        @if($user->tenant)
                            <span class="badge-{{ strtolower($user->tenant->subscription_type) }}">
                                {{ $user->tenant->subscription_type }}
                            </span>
                        @else
                            <span style="color:#aaa;">—</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="{{ route('super-admin.users.edit', $user) }}" class="sa-btn sa-btn-outline sa-btn-sm">✏️ Edit</a>
                        <form method="POST" action="{{ route('super-admin.users.delete', $user) }}" style="margin:0;"
                              onsubmit="return confirm('Delete user {{ addslashes($user->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#aaa;padding:32px;">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div style="padding:16px 20px;">{{ $users->links() }}</div>
    @endif
</div>
@endsection
