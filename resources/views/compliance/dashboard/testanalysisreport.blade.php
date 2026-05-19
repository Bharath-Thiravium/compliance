@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-4">Compliance System Diagnostic Report</h1>
        </div>
    </div>

    <!-- Health Score Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">System Health Score</h6>
                    <div class="display-4 font-weight-bold" id="healthScore">
                        {{ $report['health_score'] ?? 0 }}%
                    </div>
                    <p class="text-muted mt-2">
                        <span class="badge badge-{{ $report['status'] === 'healthy' ? 'success' : ($report['status'] === 'warning' ? 'warning' : 'danger') }}">
                            {{ strtoupper($report['status'] ?? 'unknown') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Component Summary</h6>
                    <div class="mb-2">
                        <small class="text-success font-weight-bold">
                            ✓ {{ $report['summary']['components_passed'] ?? 0 }} Passed
                        </small>
                    </div>
                    <div class="mb-2">
                        <small class="text-danger font-weight-bold">
                            ✗ {{ $report['summary']['components_failed'] ?? 0 }} Failed
                        </small>
                    </div>
                    <div>
                        <small class="text-warning font-weight-bold">
                            ⚠ {{ $report['summary']['total_issues'] ?? 0 }} Issues
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Execution Details</h6>
                    <div class="mb-2">
                        <small class="text-muted">
                            <strong>Time:</strong> {{ $report['execution_time'] ?? 0 }}ms
                        </small>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">
                            <strong>Timestamp:</strong> {{ $report['timestamp'] ?? 'N/A' }}
                        </small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                            Refresh Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Component Diagnostics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Component Diagnostics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Component</th>
                                    <th>Status</th>
                                    <th>Weight</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['diagnostics'] ?? [] as $name => $diagnostic)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $name)) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $diagnostic['status'] === 'pass' ? 'success' : 'danger' }}">
                                            {{ strtoupper($diagnostic['status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $diagnostic['weight'] ?? 0 }}%</span>
                                    </td>
                                    <td>
                                        @if($name === 'preview_pipeline')
                                            {{ $diagnostic['forms_passed'] ?? 0 }}/{{ $diagnostic['forms_tested'] ?? 0 }} forms passed
                                        @elseif($name === 'generators')
                                            {{ $diagnostic['valid_generators'] ?? 0 }}/{{ $diagnostic['total_generators'] ?? 0 }} valid
                                        @elseif($name === 'blade_templates')
                                            {{ $diagnostic['valid_templates'] ?? 0 }}/{{ $diagnostic['total_templates'] ?? 0 }} valid
                                        @elseif($name === 'api_services')
                                            {{ $diagnostic['valid_services'] ?? 0 }}/{{ $diagnostic['total_services'] ?? 0 }} valid
                                        @elseif($name === 'database_datasets')
                                            {{ $diagnostic['tables_valid'] ?? 0 }}/{{ $diagnostic['tables_checked'] ?? 0 }} tables valid
                                        @elseif($name === 'pdf_generation')
                                            {{ $diagnostic['forms_passed'] ?? 0 }}/{{ $diagnostic['forms_tested'] ?? 0 }} forms passed
                                        @elseif($name === 'inspection_pack')
                                            ZIP: {{ $diagnostic['zip_created'] ? 'Created' : 'Failed' }}
                                        @elseif($name === 'security')
                                            {{ count($diagnostic['issues'] ?? []) }} issues
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Root Cause Analysis -->
    @if(!empty($report['root_causes']))
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Root Cause Analysis</h5>
                </div>
                <div class="card-body">
                    @foreach($report['root_causes'] as $issue)
                    <div class="alert alert-{{ $issue['severity'] === 'critical' ? 'danger' : 'warning' }} mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>{{ $issue['component'] }}</strong>
                                @if(isset($issue['form_code']))
                                    <br><small class="text-muted">{{ $issue['form_code'] }}</small>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <strong>Root Cause:</strong>
                                <br>{{ $issue['root_cause'] ?? $issue['issue'] ?? 'Unknown' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Affected Files:</strong>
                                <br>
                                @foreach($issue['affected_files'] ?? [] as $file)
                                    <small class="d-block text-monospace">{{ $file }}</small>
                                @endforeach
                            </div>
                            <div class="col-md-2">
                                <strong>Recommended Fix:</strong>
                                <br><small>{{ $issue['recommended_fix'] ?? 'Review implementation' }}</small>
                            </div>
                        </div>
                        @if(isset($issue['error_message']))
                            <div class="mt-2">
                                <small class="text-muted">
                                    <strong>Error:</strong> {{ $issue['error_message'] }}
                                </small>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Amazon Q Integration -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Automated Fixes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Use Amazon Q to automatically fix detected issues. Copy the root cause analysis above and ask Amazon Q to implement the recommended fixes.
                    </p>
                    <button class="btn btn-primary" onclick="copyDiagnosticsToClipboard()">
                        Copy Diagnostics for Amazon Q
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyDiagnosticsToClipboard() {
    const diagnostics = @json($report);
    const text = JSON.stringify(diagnostics, null, 2);
    navigator.clipboard.writeText(text).then(() => {
        alert('Diagnostics copied to clipboard. Paste in Amazon Q chat.');
    });
}
</script>
@endsection
