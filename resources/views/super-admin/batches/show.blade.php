@extends('super-admin.layout')

@section('title', 'Batch Detail')
@section('page-title', 'Batch Detail')

@section('content')
    <div class="mb-3">
        <a href="{{ route('super-admin.batches.index') }}" class="btn btn-default btn-sm">← Back to All Batches</a>
    </div>

    {{-- Batch Info --}}
    <div class="card mb-4">
        <div class="card-header">📦 Batch #{{ $batch->user_batch_number ?? $batch->id }}
            <small class="text-muted">(System ID: {{ $batch->id }})</small>
        </div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-3">
                    <p><strong>User:</strong> {{ optional($batch->creator)->name ?? '—' }}<br>
                    <small class="text-muted">{{ optional($batch->creator)->email ?? '' }}</small></p>
                    <p><strong>Tenant:</strong> {{ optional($batch->tenant)->name ?? '—' }}</p>
                </div>
                <div class="grid-col col-1-3">
                    <p><strong>Period:</strong>
                        @if($batch->period_month && $batch->period_year)
                            {{ \Carbon\Carbon::createFromDate($batch->period_year, $batch->period_month, 1)->format('F Y') }}
                        @else —
                        @endif
                    </p>
                    <p><strong>Status:</strong>
                        @php
                            $badge = match($batch->status) {
                                'completed'  => 'badge-success',
                                'partial'    => 'badge-warning',
                                'failed'     => 'badge-danger',
                                'processing' => 'badge-info',
                                default      => 'badge-default',
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($batch->status) }}</span>
                    </p>
                </div>
                <div class="grid-col col-1-3">
                    <p><strong>Created:</strong> {{ $batch->created_at->format('d M Y, h:i A') }}</p>
                    <p><strong>Processed:</strong> {{ $batch->processed_at ? $batch->processed_at->format('d M Y, h:i A') : '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Forms --}}
    <div class="card">
        <div class="card-header">📋 Forms ({{ $forms->count() }})</div>
        <div class="card-body">
            @if($forms->count())
                <div class="mobile-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Form Code</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th>File</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forms as $form)
                                <tr>
                                    <td><strong>{{ $form->form_code }}</strong></td>
                                    <td>{{ $form->section ?? '—' }}</td>
                                    <td>
                                        @php
                                            $fb = match($form->status) {
                                                'generated'  => 'badge-success',
                                                'failed'     => 'badge-danger',
                                                'processing' => 'badge-info',
                                                default      => 'badge-default',
                                            };
                                        @endphp
                                        <span class="badge {{ $fb }}">{{ ucfirst($form->status) }}</span>
                                    </td>
                                    <td>
                                        @if($form->file_path)
                                            <small class="text-muted">{{ basename($form->file_path) }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $form->updated_at?->diffForHumans() ?? '—' }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No forms attached to this batch.</p>
            @endif
        </div>
    </div>
@endsection
