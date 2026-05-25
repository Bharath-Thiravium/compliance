@extends('super-admin.layout')
@section('title', 'New Tenant')
@section('page-title', 'New Tenant')

@section('content')
<div class="card" style="max-width:620px;">
    <div class="card-header flex-between">
        <span>Create Tenant, Branch &amp; Admin User</span>
        <a href="{{ route('super-admin.tenants') }}" class="btn btn-sm">← Back</a>
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

        <form method="POST" action="{{ route('super-admin.tenants.store') }}">
            @csrf

            <div class="section-title">Company Details</div>

            <div class="form-group">
                <label class="form-label">Company Name <span class="form-required">*</span></label>
                <input type="text" name="company_name" class="form-input" value="{{ old('company_name') }}" placeholder="e.g. Acme Industries Pvt Ltd" required>
            </div>

            <div class="form-group">
                <label class="form-label">Subscription Plan <span class="form-required">*</span></label>
                <select name="subscription_type" class="form-select" required>
                    <option value="FULL"    {{ old('subscription_type', 'FULL') === 'FULL'    ? 'selected' : '' }}>FULL — All features</option>
                    <option value="MINIMAL" {{ old('subscription_type') === 'MINIMAL' ? 'selected' : '' }}>MINIMAL — Core only</option>
                </select>
            </div>

            <div class="section-title">Branch / Unit Details</div>
            <p class="text-muted text-sm mb-3">A default branch is required to generate compliance batches.</p>

            <div class="form-group">
                <label class="form-label">Branch / Unit Name <span class="form-required">*</span></label>
                <input type="text" name="branch_name" class="form-input" value="{{ old('branch_name') }}" placeholder="e.g. Head Office / Unit 1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Factory License Number <span class="text-muted">(optional)</span></label>
                <input type="text" name="factory_license_number" class="form-input" value="{{ old('factory_license_number') }}" placeholder="e.g. TN/FAC/2024/001">
            </div>

            <div class="form-group">
                <label class="form-label">Branch Address <span class="text-muted">(optional)</span></label>
                <input type="text" name="address" class="form-input" value="{{ old('address') }}" placeholder="e.g. 123 Industrial Area, Chennai">
            </div>

            <div class="section-title">Admin User</div>

            <div class="form-group">
                <label class="form-label">Admin Name <span class="form-required">*</span></label>
                <input type="text" name="admin_name" class="form-input" value="{{ old('admin_name') }}" placeholder="Full name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Admin Email <span class="form-required">*</span></label>
                <input type="email" name="admin_email" class="form-input" value="{{ old('admin_email') }}" placeholder="admin@company.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Admin Password <span class="form-required">*</span></label>
                <input type="password" name="admin_password" class="form-input" placeholder="Min. 8 characters" required>
            </div>

            <div class="flex-wrap-gap mt-2">
                <button type="submit" class="btn btn-accent">✅ Create Tenant</button>
                <a href="{{ route('super-admin.tenants') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
