<div class="recent-batches-wrapper mt-4">
    <div class="card">
        <div class="card-header">📜 Recent Batches</div>
        <div class="card-body">
            @if (count($batches) > 0)
                <div class="table-wrap">
                    <table class="data-table">
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
                                    <td><strong>#{{ $batch->user_batch_number ?? $batch->id }}</strong></td>
                                    <td>{{ $batch->section->section_name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($batch->period_month && $batch->period_year)
                                            <small>{{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}</small>
                                        @else
                                            <small>{{ \Carbon\Carbon::parse($batch->period_from)->format('M d') }} - {{ \Carbon\Carbon::parse($batch->period_to)->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-success">Completed</span></td>
                                    <td><small>{{ $batch->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <a href="{{ route('compliance.batch.download', $batch->id) }}" class="btn btn-info btn-sm">
                                            📥 Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">
                    No batches created yet. Create your first batch above!
                </p>
            @endif
        </div>
    </div>
</div>
