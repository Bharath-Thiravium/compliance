// Compliance Dashboard JavaScript - Fixed Workflow
// Handles: Batch creation, review, processing, polling, and preview

const DashboardState = {
    pollingIntervals: {},
    currentBatchId: null
};

// ============================================================================
// 1. BATCH CREATION
// ============================================================================
document.getElementById('batchForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const month = document.getElementById('period_month').value;
    const year = document.getElementById('period_year').value;
    const btn = document.getElementById('createBatchBtn');
    const spinner = document.getElementById('submitSpinner');

    if (!month || !year) {
        alert('Please select both month and year');
        return;
    }

    btn.disabled = true;
    spinner.classList.remove('d-none');

    fetch('/compliance/batch/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ period_month: month, period_year: year })
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
        return r.json();
    })
    .then(data => {
        if (data.status === 'success') {
            renderBatchReview(data);
            document.getElementById('batchForm').reset();
        } else {
            alert('Error: ' + (data.message || 'Failed to create batch'));
        }
    })
    .catch(err => {
        console.error('Batch creation error:', err);
        alert('Error: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('d-none');
    });
});

// ============================================================================
// 2. BATCH REVIEW RENDERING
// ============================================================================
function renderBatchReview(data) {
    const html = `
        <div class="batch-review-wrapper" data-batch-id="${data.batch_id}">
            <div class="ant-card mb-3">
                <div class="ant-card-head success">✅ Batch Created Successfully</div>
                <div class="ant-card-body">
                    <div class="ant-row">
                        <div class="ant-col ant-col-6">
                            <p class="mb-0"><strong>Batch ID:</strong> #${data.batch_id}</p>
                        </div>
                        <div class="ant-col ant-col-6">
                            <p class="mb-0"><strong>Period:</strong> ${data.period}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ant-card mb-3">
                <div class="ant-card-head info">📋 Forms to be Generated (${data.forms.length})</div>
                <div class="ant-card-body">
                    ${data.forms.length > 0 ? `
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="ant-table" style="font-size: 13px; margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th>Form Code</th>
                                        <th>Section</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.forms.map(f => `
                                        <tr>
                                            <td><strong>${f.form_code}</strong></td>
                                            <td>${f.section || '-'}</td>
                                            <td><span class="ant-tag ant-tag-processing">${f.status.charAt(0).toUpperCase() + f.status.slice(1)}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : `<p class="text-muted text-center" style="padding: 20px 0;">No forms detected for this period.</p>`}
                </div>
            </div>
            <div class="ant-card mb-3" id="data-availability-section">
                <div class="ant-card-head warning">📊 Data Availability Check</div>
                <div class="ant-card-body">
                    ${data.data_availability.all_data_exists ? `
                        <div class="alert alert-success mb-3">
                            <strong>✅ All Required Data Available</strong>
                            <p class="mb-0 mt-2">The system has all necessary data to generate the forms. You can proceed directly.</p>
                        </div>
                    ` : `
                        <div class="alert alert-warning mb-3">
                            <strong>⚠️ Missing Data Detected</strong>
                            <p class="mb-0 mt-2">The following data sources are empty or incomplete:</p>
                            <ul class="mb-0 mt-2">
                                ${data.data_availability.missing_data.map(m => `<li>${m.charAt(0).toUpperCase() + m.slice(1).replace(/_/g, ' ')}</li>`).join('')}
                            </ul>
                        </div>
                    `}
                    <div class="mt-3">
                        <p class="mb-2"><strong>Data Summary:</strong></p>
                        <table class="ant-table" style="font-size: 12px; margin-bottom: 0;">
                            <tbody>
                                ${Object.entries(data.data_availability.data_summary).map(([key, count]) => `
                                    <tr>
                                        <td style="width: 60%;"><strong>${key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')}</strong></td>
                                        <td style="text-align: right;">
                                            <span class="ant-tag ${parseInt(count) > 0 ? 'ant-tag-success' : 'ant-tag-error'}">
                                                ${parseInt(count)} records
                                            </span>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="ant-card">
                <div class="ant-card-body">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="ant-btn ant-btn-default cancel-batch-btn" data-batch="${data.batch_id}">
                            ❌ Cancel
                        </button>
                        <button type="button" class="ant-btn ant-btn-primary proceed-batch-btn" data-batch="${data.batch_id}" ${!data.can_proceed ? 'disabled' : ''}>
                            ✅ Proceed to Generate
                        </button>
                    </div>
                    ${!data.can_proceed ? `<p class="text-muted text-center mt-2" style="font-size: 12px;">Proceed button will be enabled once all required data is provided.</p>` : ''}
                </div>
            </div>
        </div>
    `;
    document.getElementById('batch-review-container').innerHTML = html;
}

// ============================================================================
// 3. DELEGATED EVENT LISTENERS
// ============================================================================
document.addEventListener('click', function(e) {
    // PROCEED BUTTON - Start processing
    if (e.target.classList.contains('proceed-batch-btn')) {
        handleProceedBatch(e.target);
    }

    // CANCEL BUTTON - Clear review
    if (e.target.classList.contains('cancel-batch-btn')) {
        document.getElementById('batch-review-container').innerHTML = '';
    }

    // PREVIEW BUTTON - Open preview modal
    if (e.target.classList.contains('preview-btn')) {
        const batchId = e.target.dataset.batch;
        const formCode = e.target.dataset.form;
        openPreview(batchId, formCode);
    }

    // RE-AUDIT BUTTON - Fix violations
    if (e.target.classList.contains('re-audit-btn')) {
        handleReAudit(e.target);
    }
});

// ============================================================================
// 4. PROCEED BUTTON HANDLER
// ============================================================================
function handleProceedBatch(btn) {
    const batchId = btn.dataset.batch;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting...';

    fetch(`/compliance/batch/${batchId}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
    })
    .then(data => {
        if (data.status === 'success') {
            startProcessing(batchId);
            loadTimelineStatus(batchId);
        } else {
            alert(data.message || 'Failed to start batch processing');
            btn.disabled = false;
            btn.innerHTML = '✅ Proceed to Generate';
        }
    })
    .catch(err => {
        alert('Processing error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '✅ Proceed to Generate';
    });
}

// ============================================================================
// 5. PROCESSING UI - Replace data availability section
// ============================================================================
function startProcessing(batchId) {
    DashboardState.currentBatchId = batchId;

    // Replace data availability section with processing UI
    const dataAvailSection = document.getElementById('data-availability-section');
    if (dataAvailSection) {
        dataAvailSection.innerHTML = `
            <div class="ant-card-head info">⏳ Processing Batch #${batchId}</div>
            <div class="ant-card-body">
                <div style="margin-bottom:20px">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <strong>Progress</strong>
                        <span id="progress-text" class="ant-tag ant-tag-processing">0/0 forms generated</span>
                    </div>
                    <div class="progress" style="height:24px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%; font-weight:bold;">
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
                <div id="completion-message" class="ant-alert ant-alert-success" style="display:none; margin-top:20px;">
                    <strong>✅ All Forms Generated Successfully</strong>
                </div>
            </div>
        `;
        dataAvailSection.className = 'ant-card mb-3';
    }

    // Start polling
    pollBatchStatus(batchId);
}

// ============================================================================
// 6. POLLING - Update progress correctly
// ============================================================================
function pollBatchStatus(batchId) {
    // Clear any existing interval for this batch
    if (DashboardState.pollingIntervals[batchId]) {
        clearInterval(DashboardState.pollingIntervals[batchId]);
    }

    function updateUI(forms) {
        const generated = forms.filter(f => f.status === 'generated').length;
        const total = forms.length;
        const percent = total ? Math.round((generated / total) * 100) : 0;

        // Update progress bar
        const progressBar = document.getElementById('progress-bar');
        if (progressBar) {
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
        }

        // Update progress text
        const progressText = document.getElementById('progress-text');
        if (progressText) {
            progressText.textContent = `${generated}/${total} forms generated`;
        }

        // Update forms table
        const tbody = document.getElementById('forms-table-body');
        if (tbody) {
            tbody.innerHTML = forms.map(form => {
                let statusBadge = '';
                let actionBtn = '';

                if (form.status === 'generated') {
                    statusBadge = '<span class="ant-tag ant-tag-success">✅ Generated</span>';
                    actionBtn = `<button class="ant-btn ant-btn-primary ant-btn-sm preview-btn" data-batch="${batchId}" data-form="${form.form_code}">👁️ Preview</button>`;
                } else if (form.status === 'processing') {
                    statusBadge = '<span class="ant-tag ant-tag-processing">⏳ Processing</span>';
                    actionBtn = '<span class="text-muted">-</span>';
                } else {
                    statusBadge = '<span class="ant-tag">⏸️ Pending</span>';
                    actionBtn = '<span class="text-muted">-</span>';
                }

                return `
                    <tr>
                        <td><strong>${form.form_code}</strong></td>
                        <td>${statusBadge}</td>
                        <td>${actionBtn}</td>
                    </tr>
                `;
            }).join('');
        }

        // Check if all generated
        if (generated === total && total > 0) {
            clearInterval(DashboardState.pollingIntervals[batchId]);
            delete DashboardState.pollingIntervals[batchId];
            const completionMsg = document.getElementById('completion-message');
            if (completionMsg) completionMsg.style.display = 'block';
            loadTimelineStatus(batchId);
        }
    }

    function poll() {
        fetch(`/compliance/batch/${batchId}/status`)
            .then(r => r.json())
            .then(updateUI)
            .catch(err => console.error('Polling error:', err));
    }

    // Start polling every 3 seconds
    DashboardState.pollingIntervals[batchId] = setInterval(poll, 3000);
    poll(); // Initial call
}

// ============================================================================
// 7. PREVIEW MODAL
// ============================================================================
function openPreview(batchId, formCode) {
    const previewModal = new bootstrap.Modal(document.getElementById('preview-modal'));
    document.getElementById('preview-title').textContent = `Preview - ${formCode}`;
    document.getElementById('preview-content').innerHTML =
        '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    fetch(`/compliance/batch/${batchId}/preview/${formCode}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('preview-content').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('preview-content').innerHTML =
                `<div class="alert alert-danger">Error loading preview: ${err.message}</div>`;
        });

    previewModal.show();
}

// ============================================================================
// 11. TIMELINE STATUS
// ============================================================================
function loadTimelineStatus(batchId) {
    fetch(`/compliance/batch/${batchId}/timeline-status`)
        .then(r => r.json())
        .then(data => {
            const map = {
                'timeline-total':     data.total,
                'timeline-pending':   data.pending,
                'timeline-generated': data.generated,
                'timeline-verified':  data.verified,
                'timeline-overdue':   data.overdue,
            };
            Object.entries(map).forEach(([id, val]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            });
        })
        .catch(err => console.error('Timeline status error:', err));
}

// ============================================================================
function handleReAudit(btn) {
    const batchId = btn.dataset.batch;
    const formCode = btn.dataset.form;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Fixing...';

    fetch(`/compliance/batch/${batchId}/fix-violations/${formCode}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
        return r.json();
    })
    .then(data => {
        if (data.status === 'requires_input') {
            showFixModal(batchId, formCode, data.missing_fields, btn);
        } else if (data.status === 'success') {
            updateAuditUI(batchId, formCode, data, btn);
            alert('✅ Form corrected successfully!');
        } else if (data.status === 'no_violations') {
            alert('ℹ️ No violations found for this form.');
            btn.disabled = false;
            btn.innerHTML = '🔧 Fix & Re-Audit';
        } else {
            alert('❌ Fix failed: ' + (data.message || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = '🔧 Fix & Re-Audit';
        }
    })
    .catch(err => {
        alert('❌ Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '🔧 Fix & Re-Audit';
    });
}

// ============================================================================
// 9. FIX MODAL
// ============================================================================
function showFixModal(batchId, formCode, missingFields, originalBtn) {
    const modalId = `fixModal_${batchId}_${formCode}`;
    let modal = document.getElementById(modalId);

    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">🔧 Fix Violations - ${formCode}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Please provide the following missing information:</p>
                        <form id="fixForm_${batchId}_${formCode}">
                            ${missingFields.map(field => `
                                <div class="mb-3">
                                    <label class="form-label">
                                        <strong>${field.field}</strong>
                                        <small class="text-muted d-block">${field.message}</small>
                                    </label>
                                    <input type="text" class="form-control" name="${field.field}" required>
                                </div>
                            `).join('')}
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submitFix_${batchId}_${formCode}">Submit & Regenerate</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Handle form submission
    document.getElementById(`submitFix_${batchId}_${formCode}`).onclick = function() {
        const form = document.getElementById(`fixForm_${batchId}_${formCode}`);
        const formData = new FormData(form);
        const corrections = {};

        for (let [key, value] of formData.entries()) {
            corrections[key] = value;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

        fetch(`/compliance/batch/${batchId}/submit-fix/${formCode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ corrections })
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            return r.json();
        })
        .then(data => {
            if (data.status === 'success') {
                bsModal.hide();
                updateAuditUI(batchId, formCode, data, originalBtn);
                alert('✅ Form corrected and regenerated successfully!');
            } else {
                alert('❌ Fix failed: ' + (data.message || 'Unknown error'));
                this.disabled = false;
                this.innerHTML = 'Submit & Regenerate';
            }
        })
        .catch(err => {
            alert('❌ Error: ' + err.message);
            this.disabled = false;
            this.innerHTML = 'Submit & Regenerate';
        });
    };

    originalBtn.disabled = false;
    originalBtn.innerHTML = '🔧 Fix & Re-Audit';
}

// ============================================================================
// 10. UPDATE AUDIT UI
// ============================================================================
function updateAuditUI(batchId, formCode, data, btn) {
    const modal = btn.closest('.modal-body');
    const listItem = btn.closest('.list-group-item');

    if (modal) {
        const modalHeader = modal.querySelector('h4');
        if (modalHeader) {
            modalHeader.innerHTML = `Audit Score: <strong>${data.batch_average_score}/100</strong>`;
        }

        const progressBar = modal.querySelector('.progress-bar');
        if (progressBar) {
            const barClass = data.batch_average_score >= 90 ? 'bg-success' : (data.batch_average_score >= 70 ? 'bg-warning' : 'bg-danger');
            progressBar.className = `progress-bar ${barClass}`;
            progressBar.style.width = `${data.batch_average_score}%`;
            progressBar.textContent = `${data.batch_average_score}%`;
            progressBar.setAttribute('aria-valuenow', data.batch_average_score);
        }

        const confidenceBadge = modal.querySelector('.badge');
        if (confidenceBadge) {
            const badgeClass = data.batch_average_score >= 90 ? 'bg-success' : (data.batch_average_score >= 70 ? 'bg-warning' : 'bg-danger');
            confidenceBadge.className = `badge ${badgeClass}`;
            confidenceBadge.textContent = data.confidence_label;
        }
    }

    if (listItem) {
        const statusBadge = listItem.querySelector('.badge');
        if (statusBadge) {
            statusBadge.className = `badge ${data.audit_status === 'passed' ? 'bg-success' : 'bg-danger'} ms-2`;
            statusBadge.textContent = data.audit_status.charAt(0).toUpperCase() + data.audit_status.slice(1);
        }

        const scoreBadge = listItem.querySelector('.badge.bg-secondary');
        if (scoreBadge) {
            scoreBadge.textContent = `Score: ${data.form_score}/100`;
        }

        const violationsDiv = listItem.querySelector('.mt-2');
        if (data.violations.length === 0) {
            if (violationsDiv) {
                violationsDiv.innerHTML = '<small class="text-success">✅ No violations detected</small>';
            }
        } else {
            const violationsList = listItem.querySelector('ul');
            if (violationsList) {
                violationsList.innerHTML = data.violations.map(v => `
                    <li>
                        <small>
                            <strong>${v.field || 'Unknown'}</strong>
                            (${v.type || 'general'}):
                            ${v.message || 'No details'}
                        </small>
                    </li>
                `).join('');
            }
        }
    }

    const tableRow = document.querySelector(`tr[data-batch-id="${batchId}"]`);
    if (tableRow) {
        const scoreCell = tableRow.querySelector('.batch-score-badge');
        if (scoreCell) {
            const scoreClass = data.batch_average_score >= 90 ? 'ant-tag-success' : (data.batch_average_score >= 70 ? 'ant-tag-warning' : 'ant-tag-error');
            scoreCell.className = `ant-tag batch-score-badge ${scoreClass}`;
            scoreCell.style.fontWeight = 'bold';
            scoreCell.textContent = `${data.batch_average_score}/100`;
        }
    }
}
