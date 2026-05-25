<!-- Audit Modal Section -->
@foreach ($batches as $batch)
    @if (isset($batch->audit_score))
        <div class="modal fade" id="auditModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><strong>🔍 Audit Details - Batch #{{ $batch->id }}</strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div style="margin-bottom:16px;">
                            <h4 style="margin:0 0 8px;">Audit Score: <strong>{{ $batch->audit_score }}/100</strong></h4>
                            @php
                                $confidenceLabel =
                                    $batch->audit_score >= 90
                                        ? 'Inspection Ready'
                                        : ($batch->audit_score >= 70
                                            ? 'Moderate Risk – Review Recommended'
                                            : 'High Risk – Immediate Correction Required');
                                $confidenceClass =
                                    $batch->audit_score >= 90
                                        ? 'badge-success'
                                        : ($batch->audit_score >= 70
                                            ? 'badge-warning'
                                            : 'badge-danger');
                                $barColor =
                                    $batch->audit_score >= 90
                                        ? 'var(--color-success)'
                                        : ($batch->audit_score >= 70
                                            ? 'var(--color-warning)'
                                            : 'var(--color-danger)');
                            @endphp
                            <span class="badge {{ $confidenceClass }}" style="font-size:14px; padding:8px 12px; margin-bottom:12px;">
                                {{ $confidenceLabel }}
                            </span>
                            <div class="progress" style="height:30px; margin-top:12px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width:{{ $batch->audit_score }}%; font-weight:bold; font-size:16px; background:{{ $barColor }};"
                                    aria-valuenow="{{ $batch->audit_score }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $batch->audit_score }}%
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h5 style="margin-bottom:12px;"><strong>📋 Form-wise Audit Breakdown</strong></h5>
                        @if ($batch->audit_logs->isNotEmpty())
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($batch->audit_logs as $log)
                                    <div style="border:1px solid #d9d9d9; border-radius:6px; padding:12px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                            <div>
                                                <strong>{{ $log->form_code }}</strong>
                                                <span class="badge {{ $log->status === 'passed' ? 'badge-success' : 'badge-danger' }}" style="margin-left:8px;">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </div>
                                            <span class="badge badge-default">Score: {{ $log->audit_score }}/100</span>
                                        </div>
                                        @if ($log->violations && count($log->violations) > 0)
                                            <div style="margin-top:8px;">
                                                <strong style="color:var(--color-danger);">⚠️ Violations:</strong>
                                                <ul style="margin:8px 0 8px; padding-left:20px;">
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
                                                    <button class="btn btn-danger btn-sm re-audit-btn"
                                                        data-batch="{{ $batch->id }}"
                                                        data-form="{{ $log->form_code }}">
                                                        🔧 Fix & Re-Audit
                                                    </button>
                                                    <a href="{{ route('compliance.batch.preview', ['batch' => $batch->id, 'form' => $log->form_code]) }}"
                                                        class="btn btn-sm" style="margin-left:8px;" target="_blank">
                                                        👁️ Preview
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <small style="color:var(--color-success);">✅ No violations detected</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p style="color:#8c8c8c;">No audit logs available for this batch.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
