@extends('super-admin.layout')
@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant')

@section('content')
<div class="sa-card" style="max-width:620px;">
    <div class="sa-card-header">
        <span class="sa-card-title">Edit: {{ $tenant->name }}</span>
        <a href="{{ route('super-admin.tenants') }}" class="sa-btn sa-btn-outline sa-btn-sm">← Back</a>
    </div>
    <div class="sa-card-body">
        <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}">
            @csrf @method('PUT')

            {{-- Company --}}
            <div style="font-size:12px;font-weight:700;color:#aaa;letter-spacing:1px;text-transform:uppercase;margin-bottom:12px;">Company Details</div>

            <div class="sa-form-group">
                <label class="sa-label">Company Name <span style="color:#e94560;">*</span></label>
                <input type="text" name="name" class="sa-input" value="{{ old('name', $tenant->name) }}" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Subscription Plan <span style="color:#e94560;">*</span></label>
                <select name="subscription_type" class="sa-select" required>
                    <option value="FULL"    {{ old('subscription_type', $tenant->subscription_type) === 'FULL'    ? 'selected' : '' }}>FULL — All features</option>
                    <option value="MINIMAL" {{ old('subscription_type', $tenant->subscription_type) === 'MINIMAL' ? 'selected' : '' }}>MINIMAL — Core only</option>
                </select>
            </div>

            {{-- Branch --}}
            <div style="font-size:12px;font-weight:700;color:#aaa;letter-spacing:1px;text-transform:uppercase;margin:20px 0 12px;">Branch / Unit Details</div>

            @if(!$branch)
                <div class="sa-alert sa-alert-error" style="margin-bottom:14px;">
                    ⚠️ No branch found for this tenant. Fill in the branch details below to enable compliance batch creation.
                </div>
            @endif

            <div class="sa-form-group">
                <label class="sa-label">Branch / Unit Name <span style="color:#e94560;">*</span></label>
                <input type="text" name="branch_name" class="sa-input" value="{{ old('branch_name', $branch?->branch_name) }}" placeholder="e.g. Head Office / Unit 1" required>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Factory License Number <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <input type="text" name="factory_license_number" class="sa-input" value="{{ old('factory_license_number', $branch?->factory_license_number) }}" placeholder="e.g. TN/FAC/2024/001">
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Branch Address <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <input type="text" name="address" class="sa-input" value="{{ old('address', $branch?->address) }}" placeholder="e.g. 123 Industrial Area, Chennai">
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="sa-btn sa-btn-primary">💾 Save Changes</button>
                <a href="{{ route('super-admin.tenants') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
