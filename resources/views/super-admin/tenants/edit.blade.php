@extends('super-admin.layout')
@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant')

@section('content')
<div class="card" style="max-width:620px;">
    <div class="card-header flex-between">
        <span>Edit: {{ $tenant->name }}</span>
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

        <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}">
            @csrf @method('PUT')

            <div class="section-title">Company Details</div>

            <div class="form-group">
                <label class="form-label">Company Name <span class="form-required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $tenant->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Subscription Plan <span class="form-required">*</span></label>
                <select name="subscription_type" class="form-select" required>
                    <option value="FULL"    {{ old('subscription_type', $tenant->subscription_type) === 'FULL'    ? 'selected' : '' }}>FULL — All features</option>
                    <option value="MINIMAL" {{ old('subscription_type', $tenant->subscription_type) === 'MINIMAL' ? 'selected' : '' }}>MINIMAL — Core only</option>
                </select>
            </div>

            <div class="section-title">Branch / Unit Details</div>

            @if(!$branch)
                <div class="alert alert-danger mb-3">
                    ⚠️ No branch found for this tenant. Fill in the branch details below to enable compliance batch creation.
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Branch / Unit Name <span class="form-required">*</span></label>
                <input type="text" name="branch_name" class="form-input" value="{{ old('branch_name', $branch?->branch_name) }}" placeholder="e.g. Head Office / Unit 1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Factory License Number <span class="text-muted">(optional)</span></label>
                <input type="text" name="factory_license_number" class="form-input" value="{{ old('factory_license_number', $branch?->factory_license_number) }}" placeholder="e.g. TN/FAC/2024/001">
            </div>

            <div class="form-group">
                <label class="form-label">Branch Address <span class="text-muted">(optional)</span></label>
                <input type="text" name="address" class="form-input" value="{{ old('address', $branch?->address) }}" placeholder="e.g. 123 Industrial Area, Chennai">
            </div>

            <div class="flex-wrap-gap mt-2">
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="{{ route('super-admin.tenants') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
