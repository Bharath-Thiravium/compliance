<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Compliance Engine - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .status-badge { font-size: 0.85rem; }
        .subscription-badge { font-size: 0.9rem; padding: 0.5rem 1rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">🏭 Compliance Engine</span>
            <div class="d-flex align-items-center gap-3">
                @if(isset($tenant))
                    <span class="badge subscription-badge {{ $tenant->subscription_type === 'FULL' ? 'bg-success' : 'bg-warning' }}">
                        Subscription: {{ $tenant->subscription_type }}
                    </span>
                @endif
                <span class="text-white">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        {{-- Tenant Information Card --}}
        @if(isset($tenant))
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">🏢 Organization Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-2"><strong>Organization:</strong><br>{{ $tenant->name ?? 'N/A' }}</p>
                        <p class="mb-2">
                            <strong>Subscription:</strong><br>
                            <span class="badge {{ ($tenant->subscription_type ?? '') === 'FULL' ? 'bg-success' : 'bg-warning' }} fs-6">
                                {{ $tenant->subscription_type ?? 'N/A' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        @if(isset($branch))
                        <p class="mb-2"><strong>Branch:</strong><br>{{ $branch->branch_name ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>License No:</strong><br>{{ $branch->factory_license_number ?? '-' }}</p>
                        @else
                        <p class="mb-2 text-muted">No branch assigned</p>
                        @endif
                    </div>
                    <div class="col-md-4">
                        @if(isset($branch))
                        <p class="mb-2"><strong>PF Code:</strong><br>{{ $branch->pf_code ?? '-' }}</p>
                        <p class="mb-2"><strong>ESI Code:</strong><br>{{ $branch->esi_code ?? '-' }}</p>
                        @endif
                        <p class="mb-2"><strong>Logged in as:</strong><br>{{ $user->name ?? Auth::user()->name }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Compliance Health Score Card --}}
        @if(isset($healthScore))
        <div class="card shadow-sm mb-4 border-{{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }}">
            <div class="card-header bg-{{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }} text-white">
                <h5 class="mb-0">💚 Compliance Health Score</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <h1 class="display-3 text-{{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }}">{{ $healthScore['percentage'] }}%</h1>
                        <span class="badge bg-{{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }} fs-5">{{ $healthScore['status'] }}</span>
                    </div>
                    <div class="col-md-8">
                        <p class="mb-2"><strong>Score Breakdown:</strong></p>
                        <ul class="list-unstyled">
                            @foreach($healthScore['breakdown'] as $metric => $score)
                                <li class="mb-1">
                                    <span class="badge bg-{{ $score >= 18 ? 'success' : ($score >= 10 ? 'warning' : 'danger') }} me-2">{{ $score }}%</span>
                                    {{ $metric }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Compliance Timeline Metrics --}}
        @if(isset($timelineMetrics))
        <div class="card shadow-sm mb-4 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📅 Compliance Timeline Status</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <h4 class="text-secondary">{{ $timelineMetrics['total'] }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col">
                        <h4 class="text-warning">{{ $timelineMetrics['pending'] }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col">
                        <h4 class="text-primary">{{ $timelineMetrics['generated'] }}</h4>
                        <small class="text-muted">Generated</small>
                    </div>
                    <div class="col">
                        <h4 class="text-success">{{ $timelineMetrics['filed'] }}</h4>
                        <small class="text-muted">Filed</small>
                    </div>
                    <div class="col">
                        <h4 class="text-danger">{{ $timelineMetrics['overdue'] }}</h4>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($tenant) && $tenant->subscription_type === 'MINIMAL')
            <div class="alert alert-warning">
                <strong>⚠️ Minimal Subscription:</strong> Automation is disabled. Please upload statutory forms manually.
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">📋 Create Compliance Batch</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('compliance.batch.create') }}" id="batchForm">
                            @csrf

                            <div class="mb-3">
                                <label for="section_id" class="form-label">Select Section</label>
                                <select class="form-select" id="section_id" name="section_id" required>
                                    <option value="">-- Select Section --</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3" id="formsContainer" style="display:none;">
                                <label class="form-label">Select Forms</label>
                                <div class="mb-2">
                                    <label class="form-check-label">
                                        <input type="checkbox" id="selectAllForms" class="form-check-input">
                                        <strong>Select All Forms</strong>
                                    </label>
                                </div>
                                <div id="formsList" class="border rounded p-3 bg-light">
                                    <p class="text-muted mb-0">Loading forms...</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="period_month" class="form-label">Month</label>
                                    <select class="form-select" id="period_month" name="period_month" required>
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
                                <div class="col-md-6">
                                    <label for="period_year" class="form-label">Year</label>
                                    <select class="form-select" id="period_year" name="period_year" required>
                                        <option value="">-- Select Year --</option>
                                        @for($year = date('Y') - 2; $year <= date('Y') + 3; $year++)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" id="createBatchBtn">
                                <span id="submitSpinner" class="spinner-border spinner-border-sm d-none"></span>
                                Create Batch
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                @if(session('batch_id'))
                    <div class="card shadow-sm mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">✅ Batch Created</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Batch ID:</strong> {{ session('batch_id') }}</p>

                            @if(session('results'))
                                <p><strong>Status:</strong> <span class="badge bg-success">Completed</span></p>
                                <div class="mt-3 d-flex gap-2">
                                    <a href="{{ route('compliance.batch.download', session('batch_id')) }}" class="btn btn-info">
                                        📥 Download Report
                                    </a>
                                    @if(isset($tenant) && $tenant->subscription_type === 'FULL')
                                        <a href="{{ route('compliance.batch.inspectionPack', session('batch_id')) }}" class="btn btn-primary">
                                            📦 Inspection Pack
                                        </a>
                                    @endif
                                </div>
                            @elseif(isset($tenant) && $tenant->subscription_type === 'MINIMAL')
                                <p><strong>Status:</strong> <span class="badge bg-warning">Awaiting Manual Upload</span></p>
                                <div id="uploadFormsSection" class="mt-3">
                                    <p class="text-muted">Upload forms manually:</p>
                                    <div id="uploadFormsList"></div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('compliance.batch.download', session('batch_id')) }}" class="btn btn-info">
                                        📥 Generate Report
                                    </a>
                                </div>
                            @else
                                <p><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
                                @if(isset($tenant) && $tenant->subscription_type === 'FULL')
                                    <div id="previewFormsSection" class="mt-3 mb-3">
                                        <p class="text-muted">Preview forms before processing:</p>
                                        <div id="previewFormsList"></div>
                                    </div>
                                    <form method="POST" action="{{ route('compliance.batch.process', session('batch_id')) }}" class="mt-3">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            ⚙️ Process Batch
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📊 Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="text-primary">{{ count($sections) }}</h3>
                                <small class="text-muted">Sections</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-success">{{ count($batches) }}</h3>
                                <small class="text-muted">Batches</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-info">{{ collect($batches)->where('status', 'completed')->count() }}</h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">📜 Recent Batches</h5>
            </div>
            <div class="card-body">
                @if(count($batches) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Section</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    <tr>
                                        <td><strong>#{{ $batch->id }}</strong></td>
                                        <td>{{ $batch->section->section_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($batch->period_month && $batch->period_year)
                                                <small>{{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}</small>
                                            @else
                                                <small>{{ \Carbon\Carbon::parse($batch->period_from)->format('M d') }} - {{ \Carbon\Carbon::parse($batch->period_to)->format('M d, Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($batch->status === 'completed')
                                                <span class="badge bg-success status-badge">Completed</span>
                                            @elseif($batch->status === 'processing')
                                                <span class="badge bg-warning status-badge">Processing</span>
                                            @else
                                                <span class="badge bg-secondary status-badge">Pending</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $batch->created_at->diffForHumans() }}</small></td>
                                        <td>
                                            @if($batch->status === 'completed')
                                                <a href="{{ route('compliance.batch.download', $batch->id) }}" class="btn btn-sm btn-info">
                                                    📥 Download
                                                </a>
                                                @if(isset($tenant) && $tenant->subscription_type === 'FULL')
                                                    <a href="{{ route('compliance.batch.inspectionPack', $batch->id) }}" class="btn btn-sm btn-primary">
                                                        📦 Inspection Pack
                                                    </a>
                                                @endif
                                            @elseif($batch->status === 'pending')
                                                <a href="{{ route('compliance.batch.download', $batch->id) }}" class="btn btn-sm btn-info">
                                                    📥 Download
                                                </a>
                                                @if(isset($tenant) && $tenant->subscription_type === 'FULL')
                                                    <form method="POST" action="{{ route('compliance.batch.process', $batch->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">⚙️ Process</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No batches created yet. Create your first batch above!</p>
                @endif
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5">
        <small>Compliance Engine Demo | Laravel 12 | Web-Based System</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('section_id').addEventListener('change', async function() {
            const sectionId = this.value;
            const formsContainer = document.getElementById('formsContainer');
            const formsList = document.getElementById('formsList');

            if (!sectionId) {
                formsContainer.style.display = 'none';
                return;
            }

            formsList.innerHTML = '<p class="text-muted mb-0">Loading forms...</p>';
            formsContainer.style.display = 'block';

            try {
                const response = await fetch(`/compliance/forms/${sectionId}`);
                const forms = await response.json();

                if (forms.length === 0) {
                    formsList.innerHTML = '<p class="text-warning mb-0">No forms available for this section.</p>';
                    return;
                }

                formsList.innerHTML = '';
                forms.forEach(form => {
                    const div = document.createElement('div');
                    div.className = 'form-check mb-2';
                    div.innerHTML = `
                        <input class="form-check-input form-checkbox" type="checkbox" name="form_ids[]" value="${form.id}" id="form${form.id}">
                        <label class="form-check-label" for="form${form.id}">
                            <strong>${form.form_code}</strong> - ${form.form_name}
                            <span class="badge bg-${form.priority === 'High' ? 'danger' : form.priority === 'Medium' ? 'warning' : 'info'} ms-2">${form.priority}</span>
                        </label>
                    `;
                    formsList.appendChild(div);
                });
            } catch (error) {
                formsList.innerHTML = '<p class="text-danger mb-0">Failed to load forms. Please try again.</p>';
            }
        });

        document.getElementById('batchForm').addEventListener('submit', function() {
            document.getElementById('submitSpinner').classList.remove('d-none');
        });

        document.getElementById('selectAllForms').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.form-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        @if(session('batch_id') && isset($tenant) && $tenant->subscription_type === 'MINIMAL')
            const batchId = {{ session('batch_id') }};
            const formIds = @json(session('form_ids', []));

            if (formIds.length > 0) {
                const uploadList = document.getElementById('uploadFormsList');
                formIds.forEach(formId => {
                    fetch(`/compliance/forms/{{ session('section_id', 1) }}`)
                        .then(r => r.json())
                        .then(forms => {
                            const form = forms.find(f => f.id == formId);
                            if (form) {
                                const div = document.createElement('div');
                                div.className = 'mb-3';
                                div.innerHTML = `
                                    <label class="form-label"><strong>${form.form_code}</strong> - ${form.form_name}</label>
                                    <input type="file" class="form-control" accept=".pdf" data-form-id="${formId}" onchange="uploadFormFile(${batchId}, ${formId}, this)">
                                    <small class="text-success d-none" id="upload-success-${formId}">✅ Uploaded</small>
                                `;
                                uploadList.appendChild(div);
                            }
                        });
                });
            }
        @endif

        @if(session('batch_id') && isset($tenant) && $tenant->subscription_type === 'FULL' && !session('results'))
            const previewBatchId = {{ session('batch_id') }};
            const previewFormIds = @json(session('form_ids', []));

            if (previewFormIds.length > 0) {
                const previewList = document.getElementById('previewFormsList');
                fetch(`/compliance/forms/{{ session('section_id', 1) }}`)
                    .then(r => r.json())
                    .then(forms => {
                        previewFormIds.forEach(formId => {
                            const form = forms.find(f => f.id == formId);
                            if (form) {
                                const div = document.createElement('div');
                                div.className = 'mb-2';
                                div.innerHTML = `
                                    <a href="/compliance/batch/${previewBatchId}/preview/${form.form_code}" 
                                       class="btn btn-sm btn-outline-primary w-100" target="_blank">
                                        👁️ Preview ${form.form_code}
                                    </a>
                                `;
                                previewList.appendChild(div);
                            }
                        });
                    });
            }
        @endif

        function uploadFormFile(batchId, formId, input) {
            if (!input.files[0]) return;

            const formData = new FormData();
            formData.append('file', input.files[0]);

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch(`/compliance/form/upload/${batchId}/${formId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (error) {
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned invalid response');
                }
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById(`upload-success-${formId}`).classList.remove('d-none');
                    input.disabled = true;
                } else {
                    alert('Upload failed: ' + data.message);
                }
            })
            .catch(e => {
                console.error('Upload error:', e);
                alert('Upload error: ' + e.message);
            });
        }
    </script>
</body>
</html>
