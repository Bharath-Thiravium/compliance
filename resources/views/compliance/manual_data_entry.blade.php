@extends('compliance.layouts.app')

@section('title', 'Manual Statutory Data Entry')
@section('page-title', 'Manual Data Entry')

@section('content')
<div class="card">
    <div class="card-header">📝 Manual Statutory Data Entry - {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</div>
    <div class="card-body">
        <form id="manualDataForm">
            @csrf

            <div class="card mb-3">
                <div class="card-header">🏢 Establishment Details</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-2">
                            <div class="form-group">
                                <label class="form-label">Establishment Name</label>
                                <input type="text" class="form-input" name="establishment[name]" value="{{ $data['establishment']['name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-2">
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea class="form-input form-textarea" name="establishment[address]" rows="2">{{ $data['establishment']['address'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="grid-row">
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">License Number</label>
                                <input type="text" class="form-input" name="establishment[license_no]" value="{{ $data['establishment']['license_no'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Nature of Work</label>
                                <input type="text" class="form-input" name="establishment[nature_of_work]" value="{{ $data['establishment']['nature_of_work'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">PF Code</label>
                                <input type="text" class="form-input" name="establishment[pf_code]" value="{{ $data['establishment']['pf_code'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">👤 Employer Details</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Occupier Name</label>
                                <input type="text" class="form-input" name="employer[occupier_name]" value="{{ $data['employer']['occupier_name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Manager Name</label>
                                <input type="text" class="form-input" name="employer[manager_name]" value="{{ $data['employer']['manager_name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-input" name="employer[contact]" value="{{ $data['employer']['contact'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">👥 Employee Summary</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Total Employees</label>
                                <input type="number" class="form-input" name="employees[total]" value="{{ $data['employees']['total'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Male Count</label>
                                <input type="number" class="form-input" name="employees[male]" value="{{ $data['employees']['male'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Female Count</label>
                                <input type="number" class="form-input" name="employees[female]" value="{{ $data['employees']['female'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Designations</label>
                                <input type="text" class="form-input" name="employees[designations]" value="{{ $data['employees']['designations'] ?? '' }}" placeholder="e.g., Worker, Supervisor">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">💰 Wage Summary</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Total Gross Wages (₹)</label>
                                <input type="number" step="0.01" class="form-input" name="wages[gross_total]" value="{{ $data['wages']['gross_total'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Total Deductions (₹)</label>
                                <input type="number" step="0.01" class="form-input" name="wages[deductions]" value="{{ $data['wages']['deductions'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Net Pay (₹)</label>
                                <input type="number" step="0.01" class="form-input" name="wages[net_pay]" value="{{ $data['wages']['net_pay'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Overtime Wages (₹)</label>
                                <input type="number" step="0.01" class="form-input" name="wages[overtime]" value="{{ $data['wages']['overtime'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">📅 Attendance Summary</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Working Days</label>
                                <input type="number" class="form-input" name="attendance[working_days]" value="{{ $data['attendance']['working_days'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Total Present Days</label>
                                <input type="number" class="form-input" name="attendance[present_days]" value="{{ $data['attendance']['present_days'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Leave Days</label>
                                <input type="number" class="form-input" name="attendance[leave_days]" value="{{ $data['attendance']['leave_days'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">⚠️ Accident Details (if applicable)</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Employee Name</label>
                                <input type="text" class="form-input" name="accidents[employee_name]" value="{{ $data['accidents']['employee_name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Incident Date</label>
                                <input type="date" class="form-input" name="accidents[incident_date]" value="{{ $data['accidents']['incident_date'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <input type="text" class="form-input" name="accidents[type]" value="{{ $data['accidents']['type'] ?? '' }}" placeholder="e.g., Minor, Major">
                            </div>
                        </div>
                        <div class="grid-col col-1-4">
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea class="form-input form-textarea" name="accidents[description]" rows="2">{{ $data['accidents']['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">🏗️ Contractor Summary (if CLRA forms selected)</div>
                <div class="card-body">
                    <div class="grid-row">
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Contractor Name</label>
                                <input type="text" class="form-input" name="contractors[name]" value="{{ $data['contractors']['name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Number of Workers</label>
                                <input type="number" class="form-input" name="contractors[workers_count]" value="{{ $data['contractors']['workers_count'] ?? '' }}">
                            </div>
                        </div>
                        <div class="grid-col col-1-3">
                            <div class="form-group">
                                <label class="form-label">Wage Amount (₹)</label>
                                <input type="number" step="0.01" class="form-input" name="contractors[wage_amount]" value="{{ $data['contractors']['wage_amount'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-start gap-2">
                <button type="submit" class="btn btn-primary">💾 Save Data</button>
                <a href="{{ route('compliance.dashboard') }}" class="btn">← Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

<div id="saveStatus" class="alert alert-success save-status-fixed d-none">
    Data saved successfully!
</div>
@endsection

@push('scripts')
<script>
document.getElementById('manualDataForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {};

    formData.forEach((value, key) => {
        const keys = key.match(/[^\[\]]+/g);
        if (keys.length === 2) {
            if (!data[keys[0]]) data[keys[0]] = {};
            data[keys[0]][keys[1]] = value;
        }
    });

    try {
        const response = await fetch('{{ route("compliance.manual-data.save", ["month" => $month, "year" => $year]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            const status = document.getElementById('saveStatus');
            status.classList.remove('d-none');
            setTimeout(() => status.classList.add('d-none'), 3000);
        } else {
            alert('Failed to save data');
        }
    } catch (error) {
        console.error('Save error:', error);
        alert('Error saving data');
    }
});
</script>
@endpush
