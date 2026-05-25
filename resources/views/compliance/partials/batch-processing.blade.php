<!-- Batch Processing Card -->
<div class="card" id="batch-processing-card">
    <div class="card-header info">
        <span>⏳ Processing Batch #<span id="processing-batch-id"></span></span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <p style="margin:0;"><strong>Progress:</strong></p>
                <p style="margin:0;"><span id="progress-text">0/0 forms generated</span></p>
            </div>
            <div class="progress" style="height:30px;">
                <div
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    id="progress-bar"
                    role="progressbar"
                    style="width:0%"
                    aria-valuenow="0"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    0%
                </div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th style="width:30%;">Form Code</th>
                        <th style="width:30%;">Status</th>
                        <th style="width:40%;">Action</th>
                    </tr>
                </thead>
                <tbody id="forms-table-body"></tbody>
            </table>
        </div>

        <div id="completion-message" class="alert alert-success mt-3" style="display:none;">
            <strong>✅ All forms have been generated successfully!</strong>
            <p style="margin:8px 0 0;">You can now preview, download, or audit the generated forms.</p>
        </div>
    </div>
</div>
