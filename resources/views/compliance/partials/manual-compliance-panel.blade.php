{{-- Manual Compliance Tasks Panel --}}
{{-- Shown only after a batch is active (JS sets data-batch-id on #manual-compliance-panel) --}}
<div id="manual-compliance-panel" class="card d-none">
    <div class="card-header info flex-between">
        <span>📋 Manual Compliance Tasks</span>
        <span id="manual-progress-badge" class="badge badge-info">0 / 0 completed</span>
    </div>
    <div class="card-body">

        <div class="mb-3">
            <div class="progress" style="height:18px;">
                <div id="manual-progress-bar"
                     class="progress-bar progress-bar-striped progress-bar-animated"
                     style="width:0%; font-size:12px; font-weight:bold; background:var(--color-info);">0%</div>
            </div>
        </div>

        <div id="manual-global-error" class="alert alert-danger d-none mb-3"></div>

        <div class="table-wrap">
            <table class="data-table text-sm">
                <thead>
                    <tr>
                        <th style="width:35%">Compliance</th>
                        <th style="width:25%">Act</th>
                        <th style="width:15%">Status</th>
                        <th style="width:25%">Action</th>
                    </tr>
                </thead>
                <tbody id="manual-tasks-body">
                    <tr><td colspan="4" class="text-center text-muted" style="padding:20px;">Loading tasks…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Upload modal for YES action --}}
<div class="modal fade" id="manualUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📎 Upload Compliance Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="manual-upload-compliance-name" class="fw-600 mb-3"></p>
                <input type="file" id="manual-upload-file" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                <div id="manual-upload-error" class="alert alert-danger d-none mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="manual-upload-submit">Upload & Complete</button>
            </div>
        </div>
    </div>
</div>
