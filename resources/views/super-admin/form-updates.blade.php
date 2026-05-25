@extends('super-admin.layout')

@section('title', 'Government Form Updates - Super Admin')
@section('page-title', 'Form Updates')

@section('content')
    <div class="page-header">
        <a href="{{ route('super-admin.dashboard') }}" class="btn">← Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-header info">📊 Government Forms Summary</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:#722ed1;">{{ $stats['total_forms'] }}</h3>
                        <p>Total Forms</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-success);">{{ $stats['active_forms'] }}</h3>
                        <p>Active Forms</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-info);">{{ $stats['recent_updates'] }}</h3>
                        <p>Recently Updated</p>
                    </div>
                </div>
            </div>
            <div class="grid-row mt-3">
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-warning);">{{ $stats['with_changes'] }}</h3>
                        <p>Forms With Changes</p>
                    </div>
                </div>
                <div class="grid-col col-1-3">
                    <div class="stat-card">
                        <h3 style="color:var(--color-danger);">{{ $stats['inactive_forms'] }}</h3>
                        <p>Inactive Forms</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($recentUpdates->count() > 0)
        <div class="card section-spacing">
            <div class="card-header warning">🔔 Recent Form Updates (Last 30 Days)</div>
            <div class="card-body">
                <div style="display:grid; gap:12px;">
                    @foreach($recentUpdates as $form)
                        <div class="card mb-2" style="border:1px solid #e8e8e8;">
                            <div class="card-body">
                                <div class="flex-between mb-2">
                                    <div>
                                        <strong style="font-size:16px;">{{ $form->form_code }}</strong>
                                        <span class="text-muted ms-2">{{ $form->form_name }}</span>
                                    </div>
                                    <span class="badge badge-warning">Updated on: {{ $form->updated_at ? $form->updated_at->format('d M Y') : 'N/A' }}</span>
                                </div>
                                @if($form->change_summary)
                                    <div class="mb-2"><strong>What Changed:</strong> {{ $form->change_summary }}</div>
                                @endif
                                @if($form->effective_date)
                                    <div class="text-muted text-sm"><strong>Effective Date:</strong> {{ $form->effective_date->format('M d, Y') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card section-spacing">
        <div class="card-header info">📋 All Government Forms ({{ $allForms->count() }})</div>
        <div class="card-body">
            @if($allForms->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Form Code</th>
                                <th>Form Name</th>
                                <th>Section</th>
                                <th>Act Type</th>
                                <th>Version</th>
                                <th>Effective Date</th>
                                <th>Source / Department</th>
                                <th>What Changed</th>
                                <th>Status</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allForms as $form)
                                <tr>
                                    <td><strong>{{ $form->form_code }}</strong></td>
                                    <td>{{ $form->form_name }}</td>
                                    <td>{{ optional($form->section)->section_name ?? 'N/A' }}</td>
                                    <td>{{ $form->act_type }}</td>
                                    <td>
                                        @if($form->version_number)
                                            <span class="badge badge-info">v{{ $form->version_number }}</span>
                                        @else
                                            <span class="badge badge-default">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($form->effective_date)
                                            {{ $form->effective_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($form->source_url)
                                            <a href="{{ $form->source_url }}" target="_blank" class="btn btn-sm btn-info">
                                                {{ $form->department_name ?? $form->source_name ?? 'View Source' }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ $form->department_name ?? $form->source_name ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($form->change_summary)
                                            <span title="{{ $form->change_summary }}" class="text-muted" style="cursor:help;">
                                                {{ Str::limit($form->change_summary, 40) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $form->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $form->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td><span class="text-muted text-sm">{{ $form->updated_at ? $form->updated_at->format('d M Y') : 'N/A' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No forms found.</p>
            @endif
        </div>
    </div>
@endsection
