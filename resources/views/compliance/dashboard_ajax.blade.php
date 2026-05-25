@extends('compliance.layouts.app')

@push('styles')
<style>
    #batch-review-container * { max-width: 100%; box-sizing: border-box; }
    #batch-review-container table { width: 100%; table-layout: fixed; }
</style>
@endpush

@section('title', 'Dashboard - Compliance Engine')

@section('content')
    @if (isset($subscription))
        <div class="ant-card">
            <div class="ant-card-head">🏢 Organization Information</div>
            <div class="ant-card-body">
                <div class="ant-row">
                    <div class="ant-col ant-col-4">
                        <p class="mb-2"><strong>Organization:</strong><br>{{ Auth::user()->tenant->name ?? 'N/A' }}</p>
                        <p class="mb-2">
                            <strong>Subscription:</strong><br>
                            <span class="ant-tag {{ $subscription === 'FULL' ? 'ant-tag-success' : 'ant-tag-default' }}">
                                {{ $subscription }}
                            </span>
                        </p>
                    </div>
                    <div class="ant-col ant-col-4">
                        @if (isset($branch))
                            <p class="mb-2"><strong>Branch:</strong><br>{{ $branch->branch_name ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>License No:</strong><br>{{ $branch->factory_license_number ?? '-' }}</p>
                        @else
                            <p class="mb-2 text-muted">No branch assigned</p>
                        @endif
                    </div>
                    <div class="ant-col ant-col-4">
                        @if (isset($branch))
                            <p class="mb-2"><strong>PF Code:</strong><br>{{ $branch->pf_code ?? '-' }}</p>
                            <p class="mb-2"><strong>ESI Code:</strong><br>{{ $branch->esi_code ?? '-' }}</p>
                        @endif
                        <p class="mb-2"><strong>Logged in as:</strong><br>{{ $user->name ?? Auth::user()->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($healthScore))
        <div class="ant-card">
            <div class="ant-card-head {{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }}">
                💚 Compliance Health Score</div>
            <div class="ant-card-body">
                <div class="ant-row align-items-center">
                    <div class="ant-col ant-col-4 text-center">
                        <h1 style="font-size: 48px; margin: 0; color: {{ $healthScore['status'] === 'Excellent' ? '#52c41a' : ($healthScore['status'] === 'Good' ? '#faad14' : '#ff4d4f') }};">
                            {{ $healthScore['percentage'] }}%</h1>
                        <span class="ant-tag {{ $healthScore['status'] === 'Excellent' ? 'ant-tag-success' : ($healthScore['status'] === 'Good' ? 'ant-tag-warning' : 'ant-tag-error') }}" style="font-size: 16px; padding: 6px 16px;">{{ $healthScore['status'] }}</span>
                    </div>
                    <div class="ant-col ant-col-8">
                        <p class="mb-2"><strong>Score Breakdown:</strong></p>
                        <ul style="list-style: none; padding: 0;">
                            @foreach ($healthScore['breakdown'] as $metric => $score)
                                <li class="mb-2">
                                    <span class="ant-tag {{ $score >= 18 ? 'ant-tag-success' : ($score >= 10 ? 'ant-tag-warning' : 'ant-tag-error') }}" style="margin-right: 8px;">{{ $score }}%</span>
                                    {{ $metric }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($timelineMetrics))
        <div class="ant-card">
            <div class="ant-card-head info">📅 Compliance Timeline Status</div>
            <div class="ant-card-body">
                <div class="ant-row text-center">
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #8c8c8c; margin: 0;">{{ $timelineMetrics['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #faad14; margin: 0;">{{ $timelineMetrics['pending'] }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #1890ff; margin: 0;">{{ $timelineMetrics['generated'] }}</h4>
                        <small class="text-muted">Generated</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #52c41a; margin: 0;">{{ $timelineMetrics['filed'] }}</h4>
                        <small class="text-muted">Filed</small>
                    </div>
                    <div class="ant-col ant-col-4">
                        <h4 style="color: #ff4d4f; margin: 0;">{{ $timelineMetrics['overdue'] }}</h4>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($subscription) && $subscription === 'MINIMAL')
        <div class="ant-alert ant-alert-warning">
            <strong>⚠️ MINIMAL Subscription:</strong> Enter statutory data manually to auto-generate forms. Upgrade to FULL for complete automation.
        </div>
    @endif

    @if (session('success'))
        <div class="ant-alert ant-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ant-alert ant-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="ant-alert ant-alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ant-row">
        <div class="ant-col ant-col-6">
            <div class="ant-card">
                <div class="ant-card-head">📋 Create Compliance Batch</div>
                <div class="ant-card-body">
                    <form id="batchForm">
                        @csrf
                        <div class="ant-row">
                            <div class="ant-col ant-col-6">
                                <div class="ant-form-item">
                                    <label for="period_month" class="ant-form-item-label">Month</label>
                                    <select class="ant-select" id="period_month" name="period_month" required>
                                        <option value="">-- Select Month --</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ant-col ant-col-6">
                                <div class="ant-form-item">
                                    <label for="period_year" class="ant-form-item-label">Year</label>
                                    <select class="ant-select" id="period_year" name="period_year" required>
                                        <option value="">-- Select Year --</option>
                                        @for ($year = date('Y') - 2; $year <= date('Y') + 3; $year++)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="ant-btn ant-btn-primary w-100" id="createBatchBtn">
                            <span id="submitSpinner" class="spinner d-none"></span>
                            Create Batch
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="ant-col ant-col-6">
            <div class="ant-card">
                <div class="ant-card-head info">📊 Quick Stats</div>
                <div class="ant-card-body">
                    <div class="ant-row text-center">
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #1890ff; margin: 0;">{{ count($sections) }}</h3>
                            <small class="text-muted">Sections</small>
                        </div>
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #52c41a; margin: 0;">{{ count($batches) }}</h3>
                            <small class="text-muted">Batches</small>
                        </div>
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #13c2c2; margin: 0;">
                                {{ collect($batches)->filter(fn($b) => in_array($b->display_status ?? $b->status, ['Completed', 'completed']))->count() }}
                            </h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Processing Batch + Manual Compliance side-by-side --}}
    <div id="batch-manual-row" style="display:flex; flex-direction:row; flex-wrap:nowrap; gap:16px; align-items:flex-start; margin-top:24px;">

        {{-- LEFT: Processing Batch --}}
        <div style="flex:0 0 60%; width:60%; max-width:60%; min-width:0; overflow:hidden;">
            <div id="batch-review-container" style="display:none; width:100%; max-width:100%; box-sizing:border-box;"></div>
        </div>

        {{-- RIGHT: Manual Compliance Tasks --}}
        <div style="flex:0 0 calc(40% - 16px); width:calc(40% - 16px); max-width:calc(40% - 16px); min-width:0;">
            @include('compliance.partials.manual-compliance-panel')
        </div>

    </div>

    <div class="ant-card mt-4">
        <div class="ant-card-head secondary">📜 Recent Batches</div>
        <div class="ant-card-body">
            @if (count($batches) > 0)
                <div style="overflow-x: auto;">
                    <table class="ant-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Section</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Audit Score</th>
                                <th>Certification</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batches as $batch)
                                <tr data-batch-id="{{ $batch->id }}">
                                    <td><strong>#{{ $batch->id }}</strong></td>
                                    <td>{{ $batch->section->section_name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($batch->period_month && $batch->period_year)
                                            <small>{{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}</small>
                                        @else
                                            <small>{{ \Carbon\Carbon::parse($batch->period_from)->format('M d') }} - {{ \Carbon\Carbon::parse($batch->period_to)->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="mb-1"><span class="ant-tag ant-tag-success">Completed</span></div>
                                        @if(isset($batch->audit_status))
                                            <div><span class="ant-tag {{ $batch->audit_status === 'Passed' ? 'ant-tag-success' : ($batch->audit_status === 'Partial' ? 'ant-tag-warning' : ($batch->audit_status === 'Not Audited' ? 'ant-tag-default' : 'ant-tag-error')) }}">Audit: {{ $batch->audit_status }}</span></div>
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($batch->audit_score))
                                            <span class="ant-tag batch-score-badge {{ $batch->audit_score >= 90 ? 'ant-tag-success' : ($batch->audit_score >= 70 ? 'ant-tag-warning' : 'ant-tag-error') }}" style="font-weight: bold;">
                                                {{ $batch->audit_score }}/100
                                            </span>
                                            <br>
                                            <button class="ant-btn ant-btn-sm ant-btn-default mt-1" data-bs-toggle="modal" data-bs-target="#auditModal{{ $batch->id }}">
                                                👁️ View
                                            </button>
                                        @else
                                            <span class="ant-tag ant-tag-default">Not Audited</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($batch->certification_status)
                                            <span class="ant-tag {{ $batch->certification_status === 'Certified' ? 'ant-tag-success' : 'ant-tag-error' }}">
                                                {{ $batch->certification_status }}
                                            </span>
                                        @else
                                            <button class="ant-btn ant-btn-sm ant-btn-primary certify-btn" data-batch="{{ $batch->id }}">
                                                Certify
                                            </button>
                                        @endif
                                    </td>
                                    <td><small>{{ $batch->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('compliance.batch.download', $batch->id) }}" class="ant-btn ant-btn-sm ant-btn-info">
                                                📥 Download
                                            </a>
                                            @if (($batch->audit_score ?? 0) >= 70 || $batch->certification_status === 'Certified')
                                            <a href="{{ route('compliance.batch.inspectionPack', $batch->id) }}" class="ant-btn ant-btn-sm ant-btn-primary">
                                                📦 CONSOLIDATED REPORTS
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center" style="padding: 32px 0;">No batches created yet. Create your first batch above!</p>
            @endif
        </div>
    </div>

    @foreach ($batches as $batch)
        @if (isset($batch->audit_score))
            <div class="modal fade" id="auditModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: #f0f2f5; border-bottom: 2px solid #d9d9d9;">
                            <h5 class="modal-title"><strong>🔍 Audit Details - Batch #{{ $batch->id }}</strong></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-4">
                                <h4 style="margin: 0 0 8px 0;">Audit Score: <strong>{{ $batch->audit_score }}/100</strong></h4>
                                @php
                                    $confidenceLabel = $batch->audit_score >= 90 ? 'Inspection Ready' : ($batch->audit_score >= 70 ? 'Moderate Risk – Review Recommended' : 'High Risk – Immediate Correction Required');
                                    $confidenceClass = $batch->audit_score >= 90 ? 'success' : ($batch->audit_score >= 70 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $confidenceClass }}" style="font-size: 14px; padding: 8px 12px; margin-bottom: 12px;">
                                    {{ $confidenceLabel }}
                                </span>
                                <div class="progress" style="height: 30px; margin-top: 12px;">
                                    <div class="progress-bar bg-{{ $confidenceClass }}" role="progressbar" style="width: {{ $batch->audit_score }}%; font-weight: bold; font-size: 16px;" aria-valuenow="{{ $batch->audit_score }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $batch->audit_score }}%
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h5 class="mb-3"><strong>📋 Form-wise Audit Breakdown</strong></h5>
                            @if ($batch->audit_logs->isNotEmpty())
                                <div class="list-group">
                                    @foreach ($batch->audit_logs as $log)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong>{{ $log->form_code }}</strong>
                                                    <span class="badge bg-{{ $log->status === 'passed' ? 'success' : 'danger' }} ms-2">
                                                        {{ ucfirst($log->status) }}
                                                    </span>
                                                </div>
                                                <span class="badge bg-secondary">Score: {{ $log->audit_score }}/100</span>
                                            </div>
                                            @if ($log->violations && count($log->violations) > 0)
                                                <div class="mt-2">
                                                    <strong class="text-danger">⚠️ Violations:</strong>
                                                    <ul class="mb-2" style="margin-top: 8px;">
                                                        @foreach ($log->violations as $violation)
                                                            <li>
                                                                <small>
                                                                    <strong>{{ $violation['field'] ?? 'Unknown' }}</strong>
                                                                    ({{ $violation['type'] ?? 'general' }}):
                                                                    {{ $violation['message'] ?? 'No details' }}
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    @if ($log->status === 'failed')
                                                        <button class="btn btn-sm btn-danger re-audit-btn" data-batch="{{ $batch->id }}" data-form="{{ $log->form_code }}">
                                                            🔧 Fix & Re-Audit
                                                        </button>
                                                        <a href="{{ route('compliance.batch.preview', ['batch' => $batch->id, 'form' => $log->form_code]) }}" class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
                                                            👁️ Preview
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <small class="text-success">✅ No violations detected</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No audit logs available for this batch.</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@push('scripts')
    <script>
        document.getElementById('batchForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const month = document.getElementById('period_month').value;
            const year = document.getElementById('period_year').value;
            const btn = document.getElementById('createBatchBtn');
            const spinner = document.getElementById('submitSpinner');
            if (!month || !year) {
                alert('Please select both month and year');
                return;
            }
            btn.disabled = true;
            spinner.classList.remove('d-none');
            try {
                const response = await fetch('{{ route("compliance.batch.create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        period_month: parseInt(month),
                        period_year: parseInt(year)
                    })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    const container = document.getElementById('batch-review-container');
                    container.innerHTML = data.review_html;
                    container.style.display = 'block';
                    document.getElementById('manual-compliance-panel').style.display = 'block';
                    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    attachBatchReviewListeners(data.batch_id);
                } else {
                    alert('Error: ' + (data.message || 'Failed to create batch'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        });
        function attachBatchReviewListeners(batchId) {
            const cancelBtn = document.querySelector('.cancel-batch-btn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    if (confirm('Cancel this batch?')) {
                        document.getElementById('batch-review-container').style.display = 'none';
                        document.getElementById('batch-review-container').innerHTML = '';
                        document.getElementById('batchForm').reset();
                    }
                });
            }
            const dataInputBtns = document.querySelectorAll('.data-input-btn');
            dataInputBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const action = this.dataset.action;
                    handleDataInput(action, batchId);
                });
            });
            const proceedBtn = document.querySelector('.proceed-batch-btn');
            if (proceedBtn) {
                proceedBtn.addEventListener('click', async function() {
                    if (this.disabled) return;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                    try {
                        const response = await fetch(`/compliance/batch/${batchId}/process`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.status === 'success') {
                            alert('Batch processed successfully!');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            alert('Error: ' + (data.message || 'Failed to process batch'));
                            this.disabled = false;
                            this.innerHTML = 'Proceed to Generate';
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                        this.disabled = false;
                        this.innerHTML = 'Proceed to Generate';
                    }
                });
            }
        }
        function handleDataInput(action, batchId) {
            const container = document.getElementById('dataInputContainer');
            switch (action) {
                case 'manual':
                    showManualDataEntry(container, batchId);
                    break;
                case 'csv':
                    showCSVUpload(container, batchId);
                    break;
                case 'pdf':
                    showPDFUpload(container, batchId);
                    break;
                case 'template':
                    downloadTemplate();
                    break;
            }
        }
        function showManualDataEntry(container, batchId) {
            container.innerHTML = '<div class="alert alert-info"><strong>Manual Data Entry</strong><p class="mb-0 mt-2">Enter employee and payroll data manually:</p></div><form id="manualDataForm" class="mt-3"><div class="mb-3"><label class="form-label">Employee Name</label><input type="text" class="form-control" name="employee_name" required></div><div class="mb-3"><label class="form-label">Employee ID</label><input type="text" class="form-control" name="employee_id" required></div><button type="submit" class="ant-btn ant-btn-primary">Save & Continue</button></form>';
            container.style.display = 'block';
        }
        function showCSVUpload(container, batchId) {
            container.innerHTML = '<div class="alert alert-info"><strong>Upload CSV File</strong><p class="mb-0 mt-2">Upload a CSV file with employee or payroll data:</p></div><form id="csvUploadForm" class="mt-3" enctype="multipart/form-data"><div class="mb-3"><label class="form-label">Select Dataset Type</label><select class="form-select" name="dataset_type" required><option value="">-- Select Type --</option><option value="employees">Employees</option><option value="payroll">Payroll</option><option value="attendance">Attendance</option></select></div><div class="mb-3"><label class="form-label">CSV File</label><input type="file" class="form-control" name="file" accept=".csv" required></div><button type="submit" class="ant-btn ant-btn-primary">Upload & Process</button></form>';
            container.style.display = 'block';
            document.getElementById('csvUploadForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
                try {
                    const response = await fetch(`/compliance/batch/${batchId}/upload-data`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        alert('Upload successful!');
                        const proceedBtn = document.querySelector('.proceed-batch-btn');
                        if (proceedBtn) proceedBtn.disabled = false;
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'Upload & Process';
                }
            });
        }
        function showPDFUpload(container, batchId) {
            container.innerHTML = '<div class="alert alert-info"><strong>Upload PDF Document</strong><p class="mb-0 mt-2">Upload a PDF document with compliance data:</p></div><form id="pdfUploadForm" class="mt-3" enctype="multipart/form-data"><div class="mb-3"><label class="form-label">PDF File</label><input type="file" class="form-control" name="file" accept=".pdf" required></div><button type="submit" class="ant-btn ant-btn-primary">Upload & Process</button></form>';
            container.style.display = 'block';
        }
        function downloadTemplate() {
            const csv = 'employee_id,employee_name,department,designation,salary\n1,John Doe,Engineering,Developer,50000\n2,Jane Smith,HR,Manager,60000';
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'compliance_template.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
@endpush
