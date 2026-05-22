{{-- Manual Compliance Tasks Panel --}}
{{-- Shown only after a batch is active (JS sets data-batch-id on #manual-compliance-panel) --}}
<div id="manual-compliance-panel" style="display:none;" class="ant-card">
    <div class="ant-card-head info" style="display:flex; justify-content:space-between; align-items:center;">
        <span>📋 Manual Compliance Tasks</span>
        <span id="manual-progress-badge" class="ant-tag ant-tag-processing">0 / 0 completed</span>
    </div>
    <div class="ant-card-body">

        {{-- Progress bar --}}
        <div style="margin-bottom:16px;">
            <div class="progress" style="height:18px;">
                <div id="manual-progress-bar"
                     class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                     style="width:0%; font-size:12px; font-weight:bold;">0%</div>
            </div>
        </div>

        {{-- Global error --}}
        <div id="manual-global-error" class="ant-alert ant-alert-error" style="display:none; margin-bottom:12px;"></div>

        {{-- Tasks table --}}
        <div style="overflow-x:auto;">
            <table class="ant-table" style="font-size:13px;">
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
                <p id="manual-upload-compliance-name" class="fw-bold mb-3"></p>
                <input type="file" id="manual-upload-file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <div id="manual-upload-error" class="text-danger mt-2" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="manual-upload-submit">Upload & Complete</button>
            </div>
        </div>
    </div>
</div>
