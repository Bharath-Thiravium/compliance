<!-- Quick Stats Card -->
<div class="ant-card">
    <div class="ant-card-head info">📊 Quick Stats</div>
    <div class="ant-card-body">
        <div class="ant-row text-center">
            <div class="ant-col ant-col-4">
                <h3 style="color: #1890ff; margin: 0;">{{ count($sections) }}</h3>
                <small class="text-muted">Sections</small>
            </div>
            <div class="ant-col ant-col-4">
                <h3 style="color: #52c41a; margin: 0;">{{ count($batches) }}</h3>
                <small class="text-muted">Batches</small>
            </div>
            <div class="ant-col ant-col-4">
                <h3 style="color: #13c2c2; margin: 0;">
                    {{ collect($batches)->filter(fn($b) => in_array($b->display_status ?? $b->status, ['Completed', 'completed']))->count() }}
                </h3>
                <small class="text-muted">Completed</small>
            </div>
        </div>
    </div>
</div>
