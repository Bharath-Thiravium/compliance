<!-- Quick Stats Card -->
<div class="card">
    <div class="card-header info">📊 Quick Stats</div>
    <div class="card-body">
        <div class="flex-center gap-4" style="flex-wrap:wrap; text-align:center;">
            <div>
                <h3 class="mb-0 stat-value-primary">{{ count($sections) }}</h3>
                <small class="text-muted">Sections</small>
            </div>
            <div>
                <h3 class="mb-0 stat-value-success">{{ count($batches) }}</h3>
                <small class="text-muted">Batches</small>
            </div>
            <div>
                <h3 class="mb-0 stat-value-info">
                    {{ collect($batches)->filter(fn($b) => in_array($b->display_status ?? $b->status, ['Completed', 'completed']))->count() }}
                </h3>
                <small class="text-muted">Completed</small>
            </div>
        </div>
    </div>
</div>
