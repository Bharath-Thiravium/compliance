@extends('super-admin.layout')
@section('title', 'New User')
@section('page-title', 'New User')

@section('content')
<div class="sa-card" style="max-width:500px;">
    <div class="sa-card-header">
        <span class="sa-card-title">Create User</span>
        <a href="{{ route('super-admin.users') }}" class="sa-btn sa-btn-outline sa-btn-sm">← Back</a>
    </div>
    <div class="sa-card-body">
        <form method="POST" action="{{ route('super-admin.users.store') }}">
            @csrf

            <div class="sa-form-group">
                <label class="sa-label">Tenant (Company)</label>
                <select name="tenant_id" class="sa-select" required>
                    <option value="">— Select Tenant —</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->subscription_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Full Name</label>
                <input type="text" name="name" class="sa-input" value="{{ old('name') }}" placeholder="Full name" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Email</label>
                <input type="email" name="email" class="sa-input" value="{{ old('email') }}" placeholder="user@company.com" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Password</label>
                <input type="password" name="password" class="sa-input" placeholder="Min. 8 characters" required>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="sa-btn sa-btn-accent">✅ Create User</button>
                <a href="{{ route('super-admin.users') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
