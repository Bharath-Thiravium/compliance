<!-- Compliance Timeline Status -->
<div class="card mb-3" id="timeline-status-card">
    <div class="card-header flex-between">
        <span>📅 Compliance Timeline Status</span>
        <span class="badge badge-default" id="timeline-period">—</span>
    </div>
    <div class="card-body">
        <div class="flex-center gap-4" style="flex-wrap:wrap; text-align:center;">
            <div>
                <h3 class="mb-0" id="tl-total">{{ $timeline['total'] ?? 0 }}</h3>
                <small class="text-muted">Total</small>
            </div>
            <div>
                <h3 class="mb-0 stat-value-warning" id="tl-pending">{{ $timeline['pending'] ?? 0 }}</h3>
                <small class="text-muted">Pending</small>
            </div>
            <div>
                <h3 class="mb-0 stat-value-primary" id="tl-generated">{{ $timeline['generated'] ?? 0 }}</h3>
                <small class="text-muted">Generated</small>
            </div>
            <div>
                <h3 class="mb-0 stat-value-success" id="tl-verified">{{ $timeline['verified'] ?? 0 }}</h3>
                <small class="text-muted">Verified</small>
            </div>
            <div>
                <h3 class="mb-0 stat-value-danger" id="tl-overdue">{{ $timeline['overdue'] ?? 0 }}</h3>
                <small class="text-muted">Overdue</small>
            </div>
        </div>
    </div>
</div>
