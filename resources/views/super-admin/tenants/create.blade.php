@extends('super-admin.layout')
@section('title', 'New Tenant')
@section('page-title', 'New Tenant')

@section('content')
<div class="sa-card" style="max-width:620px;">
    <div class="sa-card-header">
        <span class="sa-card-title">Create Tenant, Branch & Admin User</span>
        <a href="{{ route('super-admin.tenants') }}" class="sa-btn sa-btn-outline sa-btn-sm">← Back</a>
    </div>
    <div class="sa-card-body">
        <form method="POST" action="{{ route('super-admin.tenants.store') }}">
            @csrf

            {{-- Company --}}
            <div style="font-size:12px;font-weight:700;color:#aaa;letter-spacing:1px;text-transform:uppercase;margin-bottom:12px;">Company Details</div>

            <div class="sa-form-group">
                <label class="sa-label">Company Name <span style="color:#e94560;">*</span></label>
                <input type="text" name="company_name" class="sa-input" value="{{ old('company_name') }}" placeholder="e.g. Acme Industries Pvt Ltd" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Subscription Plan <span style="color:#e94560;">*</span></label>
                <select name="subscription_type" class="sa-select" required>
                    <option value="FULL"    {{ old('subscription_type', 'FULL') === 'FULL'    ? 'selected' : '' }}>FULL — All features</option>
                    <option value="MINIMAL" {{ old('subscription_type') === 'MINIMAL' ? 'selected' : '' }}>MINIMAL — Core only</option>
                </select>
            </div>

            {{-- Branch --}}
            <div style="font-size:12px;font-weight:700;color:#aaa;letter-spacing:1px;text-transform:uppercase;margin:20px 0 12px;">Branch / Unit Details</div>
            <p style="font-size:12px;color:#888;margin-bottom:14px;">A default branch is required to generate compliance batches.</p>

            <div class="sa-form-group">
                <label class="sa-label">Branch / Unit Name <span style="color:#e94560;">*</span></label>
                <input type="text" name="branch_name" class="sa-input" value="{{ old('branch_name') }}" placeholder="e.g. Head Office / Unit 1" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Factory License Number <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <input type="text" name="factory_license_number" class="sa-input" value="{{ old('factory_license_number') }}" placeholder="e.g. TN/FAC/2024/001">
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Branch Address <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <input type="text" name="address" class="sa-input" value="{{ old('address') }}" placeholder="e.g. 123 Industrial Area, Chennai">
            </div>

            {{-- Admin User --}}
            <div style="font-size:12px;font-weight:700;color:#aaa;letter-spacing:1px;text-transform:uppercase;margin:20px 0 12px;">Admin User</div>

            <div class="sa-form-group">
                <label class="sa-label">Admin Name <span style="color:#e94560;">*</span></label>
                <input type="text" name="admin_name" class="sa-input" value="{{ old('admin_name') }}" placeholder="Full name" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Admin Email <span style="color:#e94560;">*</span></label>
                <input type="email" name="admin_email" class="sa-input" value="{{ old('admin_email') }}" placeholder="admin@company.com" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Admin Password <span style="color:#e94560;">*</span></label>
                <input type="password" name="admin_password" class="sa-input" placeholder="Min. 8 characters" required>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="sa-btn sa-btn-accent">✅ Create Tenant</button>
                <a href="{{ route('super-admin.tenants') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
