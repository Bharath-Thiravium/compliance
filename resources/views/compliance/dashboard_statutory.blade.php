@extends('compliance.layouts.antd_base')

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
                💚 Compliance Health Score
            </div>
            <div class="ant-card-body">
                <div class="ant-row align-items-center">
                    <div class="ant-col ant-col-4 text-center">
                        <h1 style="font-size: 48px; margin: 0; color: {{ $healthScore['status'] === 'Excellent' ? '#52c41a' : ($healthScore['status'] === 'Good' ? '#faad14' : '#ff4d4f') }};">
                            {{ $healthScore['percentage'] }}%
                        </h1>
                        <span class="ant-tag {{ $healthScore['status'] === 'Excellent' ? 'ant-tag-success' : ($healthScore['status'] === 'Good' ? 'ant-tag-warning' : 'ant-tag-error') }}" style="font-size: 16px; padding: 6px 16px;">
                            {{ $healthScore['status'] }}
                        </span>
                    </div>
                    <div class="ant-col ant-col-8">
                        <p class="mb-2"><strong>Score Breakdown:</strong></p>
                        <ul style="list-style: none; padding: 0;">
                            @foreach ($healthScore['breakdown'] as $metric => $score)
                                <li class="mb-2">
                                    <span class="ant-tag {{ $score >= 18 ? 'ant-tag-success' : ($score >= 10 ? 'ant-tag-warning' : 'ant-tag-error') }}" style="margin-right: 8px;">
                                        {{ $score }}%
                                    </span>
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
                    <form method="POST" action="{{ route('compliance.batch.create') }}" id="batchForm">
                        @csrf
                        <div class="ant-form-item">
                            <label for="statutory_section" class="ant-form-item-label">Select Statutory Section</label>
                            <select class="ant-select" id="statutory_section" name="statutory_section" required>
                                <option value="">-- Select Section --</option>
                                @foreach ($statutorySections as $key => $section)
                                    <option value="{{ $key }}">{{ $section['icon'] }} {{ $section['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ant-form-item" id="formsContainer" style="display:none;">
                            <label class="ant-form-item-label">Select Forms</label>
                            <div class="mb-2">
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="selectAllForms" class="ant-checkbox">
                                    <strong>Select All Forms</strong>
                                </label>
                            </div>
                            <div id="formsList" style="border: 1px solid #d9d9d9; border-radius: 6px; padding: 16px; background: #fafafa; max-height: 300px; overflow-y: auto;">
                                <p class="text-muted" style="margin: 0;">Loading forms...</p>
                            </div>
                        </div>
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
            @if (session('batch_id'))
                <div class="ant-card" style="border: 2px solid #52c41a;">
                    <div class="ant-card-head success">✅ Batch Created</div>
                    <div class="ant-card-body">
                        <p><strong>Batch ID:</strong> {{ session('batch_id') }}</p>
                        @if (session('results'))
                            <p><strong>Status:</strong> <span class="ant-tag ant-tag-success">Completed</span></p>
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('compliance.batch.download', session('batch_id')) }}" class="ant-btn ant-btn-info">📥 Download Report</a>
                                <a href="{{ route('compliance.batch.inspectionPack', session('batch_id')) }}" class="ant-btn ant-btn-primary">📦 Consolidated Reports</a>
                            </div>
                        @elseif(isset($subscription) && $subscription === 'MINIMAL')
                            <p><strong>Status:</strong> <span class="ant-tag ant-tag-warning" id="batchStatus">Awaiting Data Entry</span></p>
                            <div class="mt-3">
                                <a href="{{ route('compliance.manual-data.show', ['month' => session('period_month', date('n')), 'year' => session('period_year', date('Y'))]) }}" class="ant-btn ant-btn-primary w-100" target="_blank">
                                    📝 Step 1: Enter Statutory Data
                                </a>
                            </div>
                            <div id="previewFormsSection" class="mt-3 mb-3">
                                <p class="text-muted">Step 2: Preview forms with entered data:</p>
                                <div id="previewFormsList"></div>
                            </div>
                            <div class="mt-3">
                                <form method="POST" action="{{ route('compliance.batch.process', session('batch_id')) }}">
                                    @csrf
                                    <button type="submit" class="ant-btn ant-btn-success w-100">⚙️ Step 3: Generate Forms</button>
                                </form>
                            </div>
                        @else
                            <p><strong>Status:</strong> <span class="ant-tag ant-tag-warning">Pending</span></p>
                            @if (isset($subscription) && $subscription === 'FULL')
                                <div id="previewFormsSection" class="mt-3 mb-3">
                                    <p class="text-muted">Preview forms before processing:</p>
                                    <div id="previewFormsList"></div>
                                </div>
                                <form method="POST" action="{{ route('compliance.batch.process', session('batch_id')) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="ant-btn ant-btn-success w-100">⚙️ Process Batch</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <div class="ant-card">
                <div class="ant-card-head info">📊 Quick Stats</div>
                <div class="ant-card-body">
                    <div class="ant-row text-center">
                        <div class="ant-col ant-col-4">
                            <h3 style="color: #1890ff; margin: 0;">{{ count($statutorySections) }}</h3>
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
                                        <span class="ant-tag ant-tag-success">Completed</span>
                                    </td>
                                    <td>
                                        @if (isset($batch->audit_score))
                                            <span class="ant-tag batch-score-badge {{ $batch->audit_score >= 90 ? 'ant-tag-success' : ($batch->audit_score >= 70 ? 'ant-tag-warning' : 'ant-tag-error') }}" style="font-weight: bold;">
                                                {{ $batch->audit_score }}/100
                                            </span>
                                        @else
                                            <span class="ant-tag ant-tag-default">N/A</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $batch->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('compliance.batch.download', $batch->id) }}" class="ant-btn ant-btn-sm ant-btn-info">
                                                📥 Download
                                            </a>
                                            <a href="{{ route('compliance.batch.inspectionPack', $batch->id) }}" class="ant-btn ant-btn-sm ant-btn-primary">
                                                📦 Reports
                                            </a>
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
@endsection

@push('scripts')
    <script>
        const statutorySections = @json($statutorySections);
        const formCodeToId = @json($formCodeToId);

        document.getElementById('statutory_section').addEventListener('change', function() {
            const sectionKey = this.value;
            const formsContainer = document.getElementById('formsContainer');
            const formsList = document.getElementById('formsList');

            if (!sectionKey) {
                formsContainer.style.display = 'none';
                return;
            }

            formsContainer.style.display = 'block';
            const section = statutorySections[sectionKey];
            const forms = section.forms;

            formsList.innerHTML = '';
            Object.entries(forms).forEach(([formCode, formName]) => {
                const formId = formCodeToId[formCode];
                if (formId) {
                    const div = document.createElement('div');
                    div.className = 'mb-2';
                    div.innerHTML = `
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input class="ant-checkbox form-checkbox" type="checkbox" name="form_ids[]" value="${formId}" id="form${formId}">
                            <span>
                                <strong>${formCode}</strong> - ${formName}
                            </span>
                        </label>
                    `;
                    formsList.appendChild(div);
                }
            });
        });

        document.getElementById('batchForm').addEventListener('submit', function() {
            document.getElementById('submitSpinner').classList.remove('d-none');
        });

        document.getElementById('selectAllForms').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.form-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
@endpush
