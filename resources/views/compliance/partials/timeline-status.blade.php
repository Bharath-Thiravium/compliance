<!-- Compliance Timeline Status -->
<div class="card border-0 shadow-sm mb-4" id="timeline-status-card">
    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">📅 Compliance Timeline Status</h6>
        <span class="badge bg-secondary" id="timeline-period">—</span>
    </div>
    <div class="card-body">
        <div class="row text-center g-3">
            <div class="col">
                <h3 class="mb-0" id="tl-total">{{ $timeline['total'] ?? 0 }}</h3>
                <small class="text-muted">Total</small>
            </div>
            <div class="col">
                <h3 class="mb-0 text-warning" id="tl-pending">{{ $timeline['pending'] ?? 0 }}</h3>
                <small class="text-muted">Pending</small>
            </div>
            <div class="col">
                <h3 class="mb-0 text-primary" id="tl-generated">{{ $timeline['generated'] ?? 0 }}</h3>
                <small class="text-muted">Generated</small>
            </div>
            <div class="col">
                <h3 class="mb-0 text-success" id="tl-verified">{{ $timeline['verified'] ?? 0 }}</h3>
                <small class="text-muted">Verified</small>
            </div>
            <div class="col">
                <h3 class="mb-0 text-danger" id="tl-overdue">{{ $timeline['overdue'] ?? 0 }}</h3>
                <small class="text-muted">Overdue</small>
            </div>
        </div>
    </div>
</div>
