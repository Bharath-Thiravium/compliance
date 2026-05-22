@extends('super-admin.layout')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="sa-card" style="max-width:500px;">
    <div class="sa-card-header">
        <span class="sa-card-title">Edit: {{ $user->name }}</span>
        <a href="{{ route('super-admin.users') }}" class="sa-btn sa-btn-outline sa-btn-sm">← Back</a>
    </div>
    <div class="sa-card-body">
        <form method="POST" action="{{ route('super-admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="sa-form-group">
                <label class="sa-label">Tenant (Company)</label>
                <select name="tenant_id" class="sa-select" required>
                    <option value="">— Select Tenant —</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ old('tenant_id', $user->tenant_id) == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->subscription_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Full Name</label>
                <input type="text" name="name" class="sa-input" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Email</label>
                <input type="email" name="email" class="sa-input" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">New Password <span style="color:#aaa;font-weight:400;">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="sa-input" placeholder="Min. 8 characters">
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="sa-btn sa-btn-primary">💾 Save Changes</button>
                <a href="{{ route('super-admin.users') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
