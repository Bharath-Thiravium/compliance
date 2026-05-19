<!-- Processing UI Section -->
<div class="ant-card">
    <div class="ant-card-head info">⏳ Processing Batch #<span id="processing-batch-id"></span></div>
    <div class="ant-card-body">
        <div style="margin-bottom:20px">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <strong>Progress</strong>
                <span id="progress-text" class="ant-tag ant-tag-processing">0/0 forms generated</span>
            </div>
            <div class="progress" style="height:24px;">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                    style="width:0%; font-weight:bold;">
                    0%
                </div>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table class="ant-table">
                <thead>
                    <tr>
                        <th style="width:35%">Form Code</th>
                        <th style="width:30%">Status</th>
                        <th style="width:35%">Action</th>
                    </tr>
                </thead>
                <tbody id="forms-table-body"></tbody>
            </table>
        </div>
        <div id="completion-message" class="ant-alert ant-alert-success"
            style="display:none; margin-top:20px;">
            <strong>✅ All Forms Generated Successfully</strong>
        </div>
    </div>
</div>
