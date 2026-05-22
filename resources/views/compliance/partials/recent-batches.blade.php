<style>
    .recent-batches-wrapper {
        display: flex;
        justify-content: center;
    }
    .recent-batches-wrapper .ant-card {
        width: 1000px;
        max-width: 1400px;
    }
    .recent-batches-wrapper .row > * {
        width: 150% !important;
        max-width: 200% !important;
    }
</style>

<div class="container mt-4">
    <div class="recent-batches-wrapper">

            <!-- Recent Batches Table Section -->
            <div class="ant-card">
                <div class="ant-card-head secondary">📜 Recent Batches</div>
                <div class="ant-card-body">
                    @if (count($batches) > 0)
                        <div style="overflow-x: auto;">
                            <table class="ant-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Section</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($batches as $batch)
                                        <tr data-batch-id="{{ $batch->id }}">
                                            <td><strong>#{{ $batch->id }}</strong></td>
                                            <td>{{ $batch->section->section_name ?? 'N/A' }}</td>
                                            <td>
                                                @if ($batch->period_month && $batch->period_year)
                                                    <small>
                                                        {{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}
                                                    </small>
                                                @else
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($batch->period_from)->format('M d') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($batch->period_to)->format('M d, Y') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="ant-tag ant-tag-success">Completed</span>
                                            </td>
                                            <td>
                                                <small>{{ $batch->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('compliance.batch.download', $batch->id) }}"
                                                       class="ant-btn ant-btn-sm ant-btn-info">
                                                        📥 Download
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center" style="padding: 32px 0;">
                            No batches created yet. Create your first batch above!
                        </p>
                    @endif
                </div>
            </div>

    </div>
</div>
