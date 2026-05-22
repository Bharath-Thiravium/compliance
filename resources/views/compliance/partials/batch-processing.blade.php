<!-- Batch Processing Card -->
<div class="ant-card" id="batch-processing-card">
    <div class="ant-card-head info">
        <span>⏳ Processing Batch #<span id="processing-batch-id"></span></span>
    </div>
    <div class="ant-card-body">
        <!-- Progress Section -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0"><strong>Progress:</strong></p>
                <p class="mb-0"><span id="progress-text">0/0 forms generated</span></p>
            </div>
            <div class="progress" style="height: 30px;">
                <div 
                    class="progress-bar progress-bar-striped progress-bar-animated" 
                    id="progress-bar" 
                    role="progressbar" 
                    style="width: 0%" 
                    aria-valuenow="0" 
                    aria-valuemin="0" 
                    aria-valuemax="100">
                    0%
                </div>
            </div>
        </div>

        <!-- Forms Table -->
        <div style="overflow-x: auto;">
            <table class="ant-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Form Code</th>
                        <th style="width: 30%;">Status</th>
                        <th style="width: 40%;">Action</th>
                    </tr>
                </thead>
                <tbody id="forms-table-body">
                    <!-- Forms will be inserted here -->
                </tbody>
            </table>
        </div>

        <!-- Completion Message -->
        <div id="completion-message" class="alert alert-success mt-4" style="display: none;">
            <strong>✅ All forms have been generated successfully!</strong>
            <p class="mb-0 mt-2">You can now preview, download, or audit the generated forms.</p>
        </div>
    </div>
</div>

