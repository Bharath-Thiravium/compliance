<!-- Batch Review Section -->
<div class="batch-review-wrapper" data-batch-id="{{ $batch_id }}">
    <div class="card mb-3">
        <div class="card-header success">✅ Batch Created Successfully</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-2">
                    <p style="margin:0;"><strong>Batch ID:</strong> #{{ $batch_id }}</p>
                </div>
                <div class="grid-col col-1-2">
                    <p style="margin:0;"><strong>Period:</strong> {{ $period }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header info">📋 Forms to be Generated ({{ count($forms) }})</div>
        <div class="card-body">
            @if (count($forms) > 0)
                <div style="max-height:300px; overflow-y:auto;">
                    <table class="data-table" style="font-size:13px; margin-bottom:0;">
                        <thead>
                            <tr>
                                <th>Form Code</th>
                                <th>Section</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($forms as $form)
                                <tr>
                                    <td><strong>{{ $form['form_code'] }}</strong></td>
                                    <td>{{ $form['section'] ?? '-' }}</td>
                                    <td><span class="badge badge-info">{{ ucfirst($form['status']) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="color:#8c8c8c; text-align:center; padding:20px 0;">No forms detected for this period.</p>
            @endif
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header warning">📊 Data Availability Check</div>
        <div class="card-body">
            @if ($data_availability['all_data_exists'])
                <div class="alert alert-success mb-3">
                    <strong>✅ All Required Data Available</strong>
                    <p style="margin:8px 0 0;">The system has all necessary data to generate the forms. You can proceed directly.</p>
                </div>
            @else
                <div class="alert alert-warning mb-3">
                    <strong>⚠️ Missing Data Detected</strong>
                    <p style="margin:8px 0 0;">The following data sources are empty or incomplete:</p>
                    <ul style="margin:8px 0 0;">
                        @foreach ($data_availability['missing_data'] as $missing)
                            <li>{{ ucfirst(str_replace('_', ' ', $missing)) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="mt-3">
                <p style="margin-bottom:8px;"><strong>Data Summary:</strong></p>
                <table class="data-table" style="font-size:12px; margin-bottom:0;">
                    <tbody>
                        @foreach ($data_availability['data_summary'] as $key => $count)
                            <tr>
                                <td style="width:60%;"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                                <td style="text-align:right;">
                                    <span class="badge {{ $count > 0 ? 'badge-success' : 'badge-danger' }}">
                                        {{ $count }} records
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="btn cancel-batch-btn" data-batch="{{ $batch_id }}">
                    ❌ Cancel
                </button>
                <button type="button" class="btn btn-primary proceed-batch-btn" data-batch="{{ $batch_id }}" {{ !$can_proceed ? 'disabled' : '' }}>
                    ✅ Proceed to Generate
                </button>
            </div>
            @if (!$can_proceed)
                <p style="color:#8c8c8c; text-align:center; margin-top:8px; font-size:12px;">Proceed button will be enabled once all required data is provided.</p>
            @endif
        </div>
    </div>
</div>
