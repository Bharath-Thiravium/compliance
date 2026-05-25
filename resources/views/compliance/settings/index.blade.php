@extends('compliance.layouts.app')

@section('title', 'Settings - Compliance Engine')
@section('page-title', 'Statutory Settings')

@section('content')
<div class="page-content-narrow">
    <div class="card">
        <div class="card-header">⚙️ Statutory Establishment Settings</div>
        <div class="card-body">

            <form method="POST" action="{{ route('compliance.settings.update') }}">
                @csrf

                <h5 class="section-title">Establishment Details</h5>

                <div class="form-group">
                    <label class="form-label" for="establishment_name">Establishment Name <span class="form-required">*</span></label>
                    <input type="text" class="form-input" id="establishment_name" name="establishment_name"
                           value="{{ old('establishment_name', $tenant->establishment_name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="factory_license_no">Factory License Number <span class="form-required">*</span></label>
                    <input type="text" class="form-input" id="factory_license_no" name="factory_license_no"
                           value="{{ old('factory_license_no', $tenant->factory_license_no ?? '') }}" required>
                </div>

                <div class="grid-row mb-3">
                    <div class="grid-col col-1-2">
                        <div class="form-group">
                            <label class="form-label" for="pf_code">PF Code</label>
                            <input type="text" class="form-input" id="pf_code" name="pf_code"
                                   value="{{ old('pf_code', $tenant->pf_code ?? '') }}">
                        </div>
                    </div>
                    <div class="grid-col col-1-2">
                        <div class="form-group">
                            <label class="form-label" for="esi_code">ESI Code</label>
                            <input type="text" class="form-input" id="esi_code" name="esi_code"
                                   value="{{ old('esi_code', $tenant->esi_code ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="labour_office_address">Labour Office Address</label>
                    <textarea class="form-input form-textarea" id="labour_office_address" name="labour_office_address" rows="2">{{ old('labour_office_address', $tenant->labour_office_address ?? '') }}</textarea>
                </div>

                <h5 class="section-title mt-4">Branch / Unit Details</h5>

                @foreach($branches as $index => $branch)
                    <div class="card mb-3 branch-card">
                        <div class="card-body">
                            <h6 class="fw-600 mb-3">Branch {{ $index + 1 }}</h6>
                            <input type="hidden" name="branches[{{ $index }}][id]" value="{{ $branch->id }}">

                            <div class="form-group">
                                <label class="form-label">Unit Name <span class="form-required">*</span></label>
                                <input type="text" class="form-input"
                                       name="branches[{{ $index }}][unit_name]"
                                       value="{{ old('branches.'.$index.'.unit_name', $branch->unit_name ?? $branch->branch_name ?? '') }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address <span class="form-required">*</span></label>
                                <textarea class="form-input form-textarea"
                                          name="branches[{{ $index }}][address]" rows="3" required>{{ old('branches.'.$index.'.address', $branch->address ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="flex-between mt-4">
                    <a href="{{ route('compliance.dashboard') }}" class="btn">← Back to Dashboard</a>
                    <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
