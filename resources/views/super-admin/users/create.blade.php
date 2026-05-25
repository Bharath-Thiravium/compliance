@extends('super-admin.layout')
@section('title', 'New User')
@section('page-title', 'New User')

@section('content')
<div class="card" style="max-width:500px;">
    <div class="card-header flex-between">
        <span>Create User</span>
        <a href="{{ route('super-admin.users') }}" class="btn btn-sm">← Back</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul style="margin:0;padding-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.users.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Tenant (Company)</label>
                <select name="tenant_id" class="form-select" required>
                    <option value="">— Select Tenant —</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->subscription_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Full name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="user@company.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Min. 8 characters" required>
            </div>

            <div class="flex-wrap-gap mt-2">
                <button type="submit" class="btn btn-accent">✅ Create User</button>
                <a href="{{ route('super-admin.users') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
