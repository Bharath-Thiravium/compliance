@extends('compliance.layouts.antd_base')

@section('title', 'Manual Statutory Data Entry')

@section('content')
<div class="ant-card">
    <div class="ant-card-head">📝 Manual Statutory Data Entry - {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</div>
    <div class="ant-card-body">
        <form id="manualDataForm">
            @csrf
            
            <div class="ant-card mb-3">
                <div class="ant-card-head">🏢 Establishment Details</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-6">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Establishment Name</label>
                                <input type="text" class="ant-input" name="establishment[name]" value="{{ $data['establishment']['name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-6">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Address</label>
                                <textarea class="ant-input" name="establishment[address]" rows="2">{{ $data['establishment']['address'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="ant-row">
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">License Number</label>
                                <input type="text" class="ant-input" name="establishment[license_no]" value="{{ $data['establishment']['license_no'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Nature of Work</label>
                                <input type="text" class="ant-input" name="establishment[nature_of_work]" value="{{ $data['establishment']['nature_of_work'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">PF Code</label>
                                <input type="text" class="ant-input" name="establishment[pf_code]" value="{{ $data['establishment']['pf_code'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ant-card mb-3">
                <div class="ant-card-head">👤 Employer Details</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Occupier Name</label>
                                <input type="text" class="ant-input" name="employer[occupier_name]" value="{{ $data['employer']['occupier_name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Manager Name</label>
                                <input type="text" class="ant-input" name="employer[manager_name]" value="{{ $data['employer']['manager_name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Contact Number</label>
                                <input type="text" class="ant-input" name="employer[contact]" value="{{ $data['employer']['contact'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ant-card mb-3">
                <div class="ant-card-head">👥 Employee Summary</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Total Employees</label>
                                <input type="number" class="ant-input" name="employees[total]" value="{{ $data['employees']['total'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Male Count</label>
                                <input type="number" class="ant-input" name="employees[male]" value="{{ $data['employees']['male'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Female Count</label>
                                <input type="number" class="ant-input" name="employees[female]" value="{{ $data['employees']['female'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Designations</label>
                                <input type="text" class="ant-input" name="employees[designations]" value="{{ $data['employees']['designations'] ?? '' }}" placeholder="e.g., Worker, Supervisor">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ant-card mb-3">
                <div class="ant-card-head">💰 Wage Summary</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Total Gross Wages (₹)</label>
                                <input type="number" step="0.01" class="ant-input" name="wages[gross_total]" value="{{ $data['wages']['gross_total'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Total Deductions (₹)</label>
                                <input type="number" step="0.01" class="ant-input" name="wages[deductions]" value="{{ $data['wages']['deductions'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Net Pay (₹)</label>
                                <input type="number" step="0.01" class="ant-input" name="wages[net_pay]" value="{{ $data['wages']['net_pay'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Overtime Wages (₹)</label>
                                <input type="number" step="0.01" class="ant-input" name="wages[overtime]" value="{{ $data['wages']['overtime'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ant-card mb-3">
                <div class="ant-card-head">📅 Attendance Summary</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Working Days</label>
                                <input type="number" class="ant-input" name="attendance[working_days]" value="{{ $data['attendance']['working_days'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Total Present Days</label>
                                <input type="number" class="ant-input" name="attendance[present_days]" value="{{ $data['attendance']['present_days'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Leave Days</label>
                                <input type="number" class="ant-input" name="attendance[leave_days]" value="{{ $data['attendance']['leave_days'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ant-card mb-3">
                <div class="ant-card-head">⚠️ Accident Details (if applicable)</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Employee Name</label>
                                <input type="text" class="ant-input" name="accidents[employee_name]" value="{{ $data['accidents']['employee_name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Incident Date</label>
                                <input type="date" class="ant-input" name="accidents[incident_date]" value="{{ $data['accidents']['incident_date'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Type</label>
                                <input type="text" class="ant-input" name="accidents[type]" value="{{ $data['accidents']['type'] ?? '' }}" placeholder="e.g., Minor, Major">
                            </div>
                        </div>
                        <div class="ant-col ant-col-3">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Description</label>
                                <textarea class="ant-input" name="accidents[description]" rows="2">{{ $data['accidents']['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ant-card mb-3">
                <div class="ant-card-head">🏗️ Contractor Summary (if CLRA forms selected)</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Contractor Name</label>
                                <input type="text" class="ant-input" name="contractors[name]" value="{{ $data['contractors']['name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Number of Workers</label>
                                <input type="number" class="ant-input" name="contractors[workers_count]" value="{{ $data['contractors']['workers_count'] ?? '' }}">
                            </div>
                        </div>
                        <div class="ant-col ant-col-4">
                            <div class="ant-form-item">
                                <label class="ant-form-item-label">Wage Amount (₹)</label>
                                <input type="number" step="0.01" class="ant-input" name="contractors[wage_amount]" value="{{ $data['contractors']['wage_amount'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="ant-btn ant-btn-primary">💾 Save Data</button>
                <a href="{{ route('compliance.dashboard') }}" class="ant-btn ant-btn-default">← Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

<div id="saveStatus" class="ant-alert ant-alert-success" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1000;">
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
            status.style.display = 'block';
            setTimeout(() => status.style.display = 'none', 3000);
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
