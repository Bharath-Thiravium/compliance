<!-- Audit Modal Section -->
@foreach ($batches as $batch)
    @if (isset($batch->audit_score))
        <div class="modal fade" id="auditModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: #f0f2f5; border-bottom: 2px solid #d9d9d9;">
                        <h5 class="modal-title"><strong>🔍 Audit Details - Batch #{{ $batch->id }}</strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <h4 style="margin: 0 0 8px 0;">Audit Score: <strong>{{ $batch->audit_score }}/100</strong>
                            </h4>
                            @php
                                $confidenceLabel =
                                    $batch->audit_score >= 90
                                        ? 'Inspection Ready'
                                        : ($batch->audit_score >= 70
                                            ? 'Moderate Risk – Review Recommended'
                                            : 'High Risk – Immediate Correction Required');
                                $confidenceClass =
                                    $batch->audit_score >= 90
                                        ? 'success'
                                        : ($batch->audit_score >= 70
                                            ? 'warning'
                                            : 'danger');
                            @endphp
                            <span class="badge bg-{{ $confidenceClass }}"
                                style="font-size: 14px; padding: 8px 12px; margin-bottom: 12px;">
                                {{ $confidenceLabel }}
                            </span>
                            <div class="progress" style="height: 30px; margin-top: 12px;">
                                <div class="progress-bar bg-{{ $confidenceClass }}" role="progressbar"
                                    style="width: {{ $batch->audit_score }}%; font-weight: bold; font-size: 16px;"
                                    aria-valuenow="{{ $batch->audit_score }}" aria-valuemin="0" aria-valuemax="100">
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
                                                <span
                                                    class="badge bg-{{ $log->status === 'passed' ? 'success' : 'danger' }} ms-2">
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
                                                                ({{ $violation['type'] ?? 'general' }})
                                                                :
                                                                {{ $violation['message'] ?? 'No details' }}
                                                            </small>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @if ($log->status === 'failed')
                                                    <button class="btn btn-sm btn-danger re-audit-btn"
                                                        data-batch="{{ $batch->id }}"
                                                        data-form="{{ $log->form_code }}">
                                                        🔧 Fix & Re-Audit
                                                    </button>
                                                    <a href="{{ route('compliance.batch.preview', ['batch' => $batch->id, 'form' => $log->form_code]) }}"
                                                        class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
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
