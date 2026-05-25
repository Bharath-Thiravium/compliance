@extends('compliance.layouts.app')

@section('title', 'Diagnostic Report')
@section('page-title', 'Compliance System Diagnostic Report')

@section('content')
<div class="grid-row mb-3">
    <div class="grid-col col-1-3">
        <div class="stat-card text-center">
            <p class="text-muted text-sm mb-2">System Health Score</p>
            <div class="stat-value">{{ $report['health_score'] ?? 0 }}%</div>
            <div class="mt-2">
                <span class="badge badge-{{ ($report['status'] ?? '') === 'healthy' ? 'success' : (($report['status'] ?? '') === 'warning' ? 'warning' : 'danger') }}">
                    {{ strtoupper($report['status'] ?? 'unknown') }}
                </span>
            </div>
        </div>
    </div>
    <div class="grid-col col-1-3">
        <div class="stat-card">
            <p class="text-muted text-sm mb-2">Component Summary</p>
            <div class="mb-1" style="color:var(--color-success);font-weight:600;">✓ {{ $report['summary']['components_passed'] ?? 0 }} Passed</div>
            <div class="mb-1" style="color:var(--color-danger);font-weight:600;">✗ {{ $report['summary']['components_failed'] ?? 0 }} Failed</div>
            <div style="color:var(--color-warning);font-weight:600;">⚠ {{ $report['summary']['total_issues'] ?? 0 }} Issues</div>
        </div>
    </div>
    <div class="grid-col col-1-3">
        <div class="stat-card">
            <p class="text-muted text-sm mb-2">Execution Details</p>
            <div class="text-sm mb-1"><strong>Time:</strong> {{ $report['execution_time'] ?? 0 }}ms</div>
            <div class="text-sm mb-2"><strong>Timestamp:</strong> {{ $report['timestamp'] ?? 'N/A' }}</div>
            <button class="btn btn-primary btn-sm" onclick="location.reload()">Refresh Report</button>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Component Diagnostics</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
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
                    <td><strong>{{ ucfirst(str_replace('_', ' ', $name)) }}</strong></td>
                    <td>
                        <span class="badge badge-{{ ($diagnostic['status'] ?? '') === 'pass' ? 'success' : 'danger' }}">
                            {{ strtoupper($diagnostic['status'] ?? '') }}
                        </span>
                    </td>
                    <td><span class="badge badge-info">{{ $diagnostic['weight'] ?? 0 }}%</span></td>
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
                            ZIP: {{ ($diagnostic['zip_created'] ?? false) ? 'Created' : 'Failed' }}
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

@if(!empty($report['root_causes']))
<div class="card mb-3">
    <div class="card-header warning">Root Cause Analysis</div>
    <div class="card-body">
        @foreach($report['root_causes'] as $issue)
        <div class="alert alert-{{ ($issue['severity'] ?? '') === 'critical' ? 'danger' : 'warning' }} mb-3">
            <div class="grid-row">
                <div class="grid-col col-1-4">
                    <strong>{{ $issue['component'] ?? '' }}</strong>
                    @if(isset($issue['form_code']))
                        <br><span class="text-muted text-xs">{{ $issue['form_code'] }}</span>
                    @endif
                </div>
                <div class="grid-col col-1-4">
                    <strong>Root Cause:</strong><br>
                    {{ $issue['root_cause'] ?? $issue['issue'] ?? 'Unknown' }}
                </div>
                <div class="grid-col col-1-4">
                    <strong>Affected Files:</strong><br>
                    @foreach($issue['affected_files'] ?? [] as $file)
                        <span class="text-xs d-block" style="font-family:monospace;">{{ $file }}</span>
                    @endforeach
                </div>
                <div class="grid-col col-1-4">
                    <strong>Recommended Fix:</strong><br>
                    <span class="text-xs">{{ $issue['recommended_fix'] ?? 'Review implementation' }}</span>
                </div>
            </div>
            @if(isset($issue['error_message']))
                <div class="mt-2 text-xs text-muted"><strong>Error:</strong> {{ $issue['error_message'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">Automated Fixes</div>
    <div class="card-body">
        <p class="text-muted mb-3">Use Amazon Q to automatically fix detected issues. Copy the root cause analysis above and ask Amazon Q to implement the recommended fixes.</p>
        <button class="btn btn-primary" onclick="copyDiagnosticsToClipboard()">Copy Diagnostics for Amazon Q</button>
    </div>
</div>

<script>
function copyDiagnosticsToClipboard() {
    const diagnostics = @json($report);
    navigator.clipboard.writeText(JSON.stringify(diagnostics, null, 2)).then(() => {
        alert('Diagnostics copied to clipboard. Paste in Amazon Q chat.');
    });
}
</script>
@endsection
