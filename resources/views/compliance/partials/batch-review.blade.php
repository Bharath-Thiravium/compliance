<!-- Batch Review Section -->
<div class="batch-review-wrapper" data-batch-id="{{ $batch_id }}">
    <div class="ant-card mb-3">
        <div class="ant-card-head success">✅ Batch Created Successfully</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-6">
                    <p class="mb-0"><strong>Batch ID:</strong> #{{ $batch_id }}</p>
                </div>
                <div class="ant-col ant-col-6">
                    <p class="mb-0"><strong>Period:</strong> {{ $period }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="ant-card mb-3">
        <div class="ant-card-head info">📋 Forms to be Generated ({{ count($forms) }})</div>
        <div class="ant-card-body">
            @if (count($forms) > 0)
                <div style="max-height: 300px; overflow-y: auto;">
                    <table class="ant-table" style="font-size: 13px; margin-bottom: 0;">
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
                                    <td><span class="ant-tag ant-tag-processing">{{ ucfirst($form['status']) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center" style="padding: 20px 0;">No forms detected for this period.</p>
            @endif
        </div>
    </div>
    <div class="ant-card mb-3">
        <div class="ant-card-head warning">📊 Data Availability Check</div>
        <div class="ant-card-body">
            @if ($data_availability['all_data_exists'])
                <div class="alert alert-success mb-3">
                    <strong>✅ All Required Data Available</strong>
                    <p class="mb-0 mt-2">The system has all necessary data to generate the forms. You can proceed directly.</p>
                </div>
            @else
                <div class="alert alert-warning mb-3">
                    <strong>⚠️ Missing Data Detected</strong>
                    <p class="mb-0 mt-2">The following data sources are empty or incomplete:</p>
                    <ul class="mb-0 mt-2">
                        @foreach ($data_availability['missing_data'] as $missing)
                            <li>{{ ucfirst(str_replace('_', ' ', $missing)) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="mt-3">
                <p class="mb-2"><strong>Data Summary:</strong></p>
                <table class="ant-table" style="font-size: 12px; margin-bottom: 0;">
                    <tbody>
                        @foreach ($data_availability['data_summary'] as $key => $count)
                            <tr>
                                <td style="width: 60%;"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                                <td style="text-align: right;">
                                    <span class="ant-tag {{ $count > 0 ? 'ant-tag-success' : 'ant-tag-error' }}">
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
    <div class="ant-card">
        <div class="ant-card-body">
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="ant-btn ant-btn-default cancel-batch-btn" data-batch="{{ $batch_id }}">
                    ❌ Cancel
                </button>
                <button type="button" class="ant-btn ant-btn-primary proceed-batch-btn" data-batch="{{ $batch_id }}" {{ !$can_proceed ? 'disabled' : '' }}>
                    ✅ Proceed to Generate
                </button>
            </div>
            @if (!$can_proceed)
                <p class="text-muted text-center mt-2" style="font-size: 12px;">Proceed button will be enabled once all required data is provided.</p>
            @endif
        </div>
    </div>
</div>
