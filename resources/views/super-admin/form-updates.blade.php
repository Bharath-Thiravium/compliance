@extends('super-admin.layouts.app')

@section('title', 'Government Form Updates - Super Admin')

@section('content')
    <div class="page-header">
        <a href="{{ route('super-admin.dashboard') }}" class="ant-btn">← Back to Dashboard</a>
    </div>

    <!-- Summary Stats -->
    <div class="ant-card">
        <div class="ant-card-head info">📊 Government Forms Summary</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #722ed1;">{{ $stats['total_forms'] }}</h3>
                        <p>Total Forms</p>
                    </div>
                </div>
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #52c41a;">{{ $stats['active_forms'] }}</h3>
                        <p>Active Forms</p>
                    </div>
                </div>
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #13c2c2;">{{ $stats['recent_updates'] }}</h3>
                        <p>Recently Updated</p>
                    </div>
                </div>
            </div>
            <div class="ant-row mt-4">
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #faad14;">{{ $stats['with_changes'] }}</h3>
                        <p>Forms With Changes</p>
                    </div>
                </div>
                <div class="ant-col ant-col-4">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $stats['inactive_forms'] }}</h3>
                        <p>Inactive Forms</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Updates Notification Panel -->
    @if($recentUpdates->count() > 0)
        <div class="ant-card section-spacing">
            <div class="ant-card-head warning">🔔 Recent Form Updates (Last 30 Days)</div>
            <div class="ant-card-body">
                <div style="display: grid; gap: 12px;">
                    @foreach($recentUpdates as $form)
                        <div class="summary-card">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <div>
                                    <strong style="font-size: 16px;">{{ $form->form_code }}</strong>
                                    <span style="margin-left: 12px; color: #8c8c8c;">{{ $form->form_name }}</span>
                                </div>
                                <span class="ant-tag ant-tag-warning">Updated on: {{ $form->updated_at ? $form->updated_at->format('d M Y') : 'N/A' }}</span>
                            </div>
                            @if($form->change_summary)
                                <div style="color: #595959; margin-bottom: 8px;">
                                    <strong>What Changed:</strong> {{ $form->change_summary }}
                                </div>
                            @endif
                            @if($form->effective_date)
                                <div style="color: #8c8c8c; font-size: 13px;">
                                    <strong>Effective Date:</strong> {{ $form->effective_date->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- All Forms Table -->
    <div class="ant-card section-spacing">
        <div class="ant-card-head info">📋 All Government Forms ({{ $allForms->count() }})</div>
        <div class="ant-card-body">
            @if($allForms->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="ant-table">
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
                                            <span class="ant-tag ant-tag-info">v{{ $form->version_number }}</span>
                                        @else
                                            <span class="ant-tag ant-tag-default">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($form->effective_date)
                                            {{ $form->effective_date->format('M d, Y') }}
                                        @else
                                            <span style="color: #8c8c8c;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($form->source_url)
                                            <a href="{{ $form->source_url }}" target="_blank" class="ant-btn ant-btn-sm" style="background: #13c2c2; color: white; border: none; padding: 4px 8px; text-decoration: none;">
                                                {{ $form->department_name ?? $form->source_name ?? 'View Source' }}
                                            </a>
                                        @else
                                            <span style="color: #8c8c8c;">{{ $form->department_name ?? $form->source_name ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($form->change_summary)
                                            <span title="{{ $form->change_summary }}" style="cursor: help; color: #595959;">
                                                {{ Str::limit($form->change_summary, 40) }}
                                            </span>
                                        @else
                                            <span style="color: #8c8c8c;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="ant-tag {{ $form->is_active ? 'ant-tag-success' : 'ant-tag-error' }}">
                                            {{ $form->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td><span style="font-size: 13px; color: #8c8c8c;">{{ $form->updated_at ? $form->updated_at->format('d M Y') : 'N/A' }}</span></td>
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
