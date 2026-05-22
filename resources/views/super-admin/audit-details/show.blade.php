@extends('super-admin.layouts.app')

@section('title', 'Audit Detail #' . $audit->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('super-admin.audit-details') }}" class="ant-btn">← Back to Audit Details</a>
    </div>

    <div class="ant-card">
        <div class="ant-card-head">🔍 Audit Record #{{ $audit->id }}</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-6">
                    <h4 style="margin-bottom: 16px;">User Information</h4>
                    <p><strong>Name:</strong> {{ optional($audit->user)->name ?? 'System' }}</p>
                    <p><strong>Email:</strong> {{ optional($audit->user)->email ?? '-' }}</p>
                    <p><strong>Role:</strong> <span class="ant-tag ant-tag-info">{{ optional($audit->user)->role ?? '-' }}</span></p>
                    <p><strong>Tenant:</strong> {{ optional($audit->tenant)->name ?? '-' }}</p>
                </div>
                <div class="ant-col ant-col-6">
                    <h4 style="margin-bottom: 16px;">Action Details</h4>
                    <p><strong>Action Type:</strong> {{ $audit->action_type }}</p>
                    <p><strong>Action Label:</strong> {{ $audit->action_label ?? '-' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="ant-tag {{ $audit->status === 'success' ? 'ant-tag-success' : 'ant-tag-error' }}">
                            {{ $audit->status }}
                        </span>
                    </p>
                    <p><strong>Form Code:</strong> {{ $audit->form_code ?? '-' }}</p>
                    <p><strong>Batch ID:</strong> {{ $audit->batch_id ? '#' . $audit->batch_id : '-' }}</p>
                    <p><strong>Section:</strong> {{ $audit->section_name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="ant-card mt-4">
        <div class="ant-card-head info">🌐 Request Information</div>
        <div class="ant-card-body">
            <p><strong>Request URL:</strong> <small>{{ $audit->request_url ?? '-' }}</small></p>
            <p><strong>Route Name:</strong> {{ $audit->route_name ?? '-' }}</p>
            <p><strong>IP Address:</strong> {{ $audit->ip_address ?? '-' }}</p>
            <p><strong>User Agent:</strong> <small>{{ $audit->user_agent ?? '-' }}</small></p>
        </div>
    </div>

    @if($audit->error_message)
        <div class="ant-card mt-4">
            <div class="ant-card-head danger">⚠️ Error Message</div>
            <div class="ant-card-body">
                <pre style="background: #f5f5f5; padding: 16px; border-radius: 6px; overflow-x: auto;">{{ $audit->error_message }}</pre>
            </div>
        </div>
    @endif

    @if($audit->old_values)
        <div class="ant-card mt-4">
            <div class="ant-card-head warning">📝 Old Values</div>
            <div class="ant-card-body">
                <pre style="background: #f5f5f5; padding: 16px; border-radius: 6px; overflow-x: auto;">{{ json_encode($audit->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    @if($audit->new_values)
        <div class="ant-card mt-4">
            <div class="ant-card-head success">✅ New Values</div>
            <div class="ant-card-body">
                <pre style="background: #f5f5f5; padding: 16px; border-radius: 6px; overflow-x: auto;">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    @if($audit->meta)
        <div class="ant-card mt-4">
            <div class="ant-card-head">📊 Metadata</div>
            <div class="ant-card-body">
                <pre style="background: #f5f5f5; padding: 16px; border-radius: 6px; overflow-x: auto;">{{ json_encode($audit->meta, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    <div class="ant-card mt-4">
        <div class="ant-card-head secondary">🕒 Timestamps</div>
        <div class="ant-card-body">
            <p><strong>Created At:</strong> {{ $audit->created_at ? $audit->created_at->format('Y-m-d H:i:s') : '-' }} ({{ $audit->created_at ? $audit->created_at->diffForHumans() : '-' }})</p>
            <p><strong>Updated At:</strong> {{ $audit->updated_at ? $audit->updated_at->format('Y-m-d H:i:s') : '-' }}</p>
        </div>
    </div>
@endsection
