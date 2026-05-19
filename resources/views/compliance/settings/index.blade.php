@extends('compliance.layouts.antd_base')

@section('title', 'Settings - Compliance Engine')

@section('content')
    <div style="max-width: 1000px; margin: 0 auto;">
        <div class="ant-card">
            <div class="ant-card-head">Statutory Establishment Settings</div>
            <div class="ant-card-body">
                @if(session('success'))
                    <div class="ant-alert ant-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="ant-alert ant-alert-error">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('compliance.settings.update') }}">
                    @csrf

                    <h5 style="border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; font-weight: 600;">Establishment Details</h5>

                    <div class="ant-form-item">
                        <label for="establishment_name" class="ant-form-item-label">Establishment Name <span style="color: #ff4d4f;">*</span></label>
                        <input type="text" class="ant-input" id="establishment_name" name="establishment_name" 
                               value="{{ old('establishment_name', $tenant->establishment_name ?? '') }}" required>
                    </div>

                    <div class="ant-form-item">
                        <label for="factory_license_no" class="ant-form-item-label">Factory License Number <span style="color: #ff4d4f;">*</span></label>
                        <input type="text" class="ant-input" id="factory_license_no" name="factory_license_no" 
                               value="{{ old('factory_license_no', $tenant->factory_license_no ?? '') }}" required>
                    </div>

                    <div class="ant-row">
                        <div class="ant-col ant-col-6">
                            <div class="ant-form-item">
                                <label for="pf_code" class="ant-form-item-label">PF Code</label>
                                <input type="text" class="ant-input" id="pf_code" name="pf_code" 
                                       value="{{ old('pf_code', $tenant->pf_code ?? '') }}">
                            </div>
                        </div>

                        <div class="ant-col ant-col-6">
                            <div class="ant-form-item">
                                <label for="esi_code" class="ant-form-item-label">ESI Code</label>
                                <input type="text" class="ant-input" id="esi_code" name="esi_code" 
                                       value="{{ old('esi_code', $tenant->esi_code ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="ant-form-item">
                        <label for="labour_office_address" class="ant-form-item-label">Labour Office Address</label>
                        <textarea class="ant-input" id="labour_office_address" name="labour_office_address" rows="2" style="height: auto; min-height: 60px;">{{ old('labour_office_address', $tenant->labour_office_address ?? '') }}</textarea>
                    </div>

                    <h5 style="border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; margin-bottom: 16px; margin-top: 32px; font-weight: 600;">Branch/Unit Details</h5>

                    @foreach($branches as $index => $branch)
                        <div class="ant-card mb-3" style="background: #fafafa;">
                            <div class="ant-card-body">
                                <h6 style="font-weight: 600; margin-bottom: 16px;">Branch {{ $index + 1 }}</h6>
                                
                                <input type="hidden" name="branches[{{ $index }}][id]" value="{{ $branch->id }}">

                                <div class="ant-form-item">
                                    <label for="branch_unit_name_{{ $index }}" class="ant-form-item-label">Unit Name <span style="color: #ff4d4f;">*</span></label>
                                    <input type="text" class="ant-input" id="branch_unit_name_{{ $index }}" 
                                           name="branches[{{ $index }}][unit_name]" 
                                           value="{{ old('branches.'.$index.'.unit_name', $branch->unit_name ?? $branch->branch_name ?? '') }}" required>
                                </div>

                                <div class="ant-form-item">
                                    <label for="branch_address_{{ $index }}" class="ant-form-item-label">Address <span style="color: #ff4d4f;">*</span></label>
                                    <textarea class="ant-input" id="branch_address_{{ $index }}" 
                                              name="branches[{{ $index }}][address]" rows="3" style="height: auto; min-height: 80px;" required>{{ old('branches.'.$index.'.address', $branch->address ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('compliance.dashboard') }}" class="ant-btn">Back to Dashboard</a>
                        <button type="submit" class="ant-btn ant-btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
