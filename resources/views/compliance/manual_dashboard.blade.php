@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Compliance Execution Dashboard</h1>
            <p class="text-muted mt-2">
                <span id="current-batch-period">Loading...</span>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <select id="batch-selector" class="form-select" style="max-width: 250px;">
                <option value="">Select a batch...</option>
            </select>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Tasks</p>
                            <h3 class="mb-0" id="stat-total">0</h3>
                        </div>
                        <span class="badge bg-primary">📋</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Completed</p>
                            <h3 class="mb-0 text-success" id="stat-completed">0</h3>
                        </div>
                        <span class="badge bg-success">✓</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pending</p>
                            <h3 class="mb-0 text-warning" id="stat-pending">0</h3>
                        </div>
                        <span class="badge bg-warning">⏳</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Skipped</p>
                            <h3 class="mb-0 text-secondary" id="stat-skipped">0</h3>
                        </div>
                        <span class="badge bg-secondary">⊘</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Timeline Status -->
    @include('compliance.partials.timeline-status', ['timeline' => []])

    <!-- Progress Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Completion Progress</h6>
                <span class="badge bg-info" id="progress-percentage">0%</span>
            </div>
            <div class="progress" style="height: 24px;">
                <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span class="d-flex align-items-center justify-content-center h-100 small fw-bold text-white" id="progress-text">0 of 0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0">Compliance Tasks</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Compliance Name</th>
                            <th>Act Name</th>
                            <th>Status</th>
                            <th>Document</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="compliance-table-body">
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Select a batch to view compliance tasks
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Compliance Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="upload-item-id" name="item_id">
                    <div class="mb-3">
                        <label for="upload-file" class="form-label">Select File</label>
                        <input type="file" class="form-control" id="upload-file" name="file"
                               accept=".pdf,.jpg,.jpeg,.png" required aria-describedby="upload-hint">
                        <small id="upload-hint" class="text-muted">Accepted: PDF, JPG, PNG (Max 5MB)</small>
                        <div id="upload-error" class="text-danger small mt-1" role="alert" aria-live="polite"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="upload-submit-btn" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap JS failed to load. Modal and UI components will not work.');
}

const API_BASE = '/compliance';
let currentBatchId = null;
let _uploadModal = null;  // single Bootstrap modal instance
const _inFlight = new Set(); // guard against duplicate requests

// ── CSRF ──────────────────────────────────────────────────────────────────────
function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) throw new Error('CSRF meta tag missing from layout.');
    return meta.content;
}

// ── XSS escape ────────────────────────────────────────────────────────────────
const esc = s => String(s ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

// ── Global fetch wrapper ──────────────────────────────────────────────────────
// Always reads body as text first, then parses — so a non-JSON response
// (HTML error page, empty body, redirect) produces a readable error message
// instead of a cryptic JSON.parse crash.
async function apiFetch(url, options = {}) {
    let res;
    try {
        res = await fetch(url, {
            ...options,
            headers: { 'X-CSRF-TOKEN': csrfToken(), ...(options.headers ?? {}) },
        });
    } catch (networkErr) {
        throw new Error('Network error — check your connection.');
    }

    const text = await res.text();
    let data;
    try {
        data = JSON.parse(text);
    } catch {
        // Backend returned non-JSON (HTML error page, empty body, redirect)
        console.error('Non-JSON response from', url, '\n', text.slice(0, 300));
        throw new Error(`Server returned an unexpected response (HTTP ${res.status}).`);
    }

    if (!res.ok || data.success === false) {
        throw new Error(data.message || `Request failed (HTTP ${res.status}).`);
    }
    return data;
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    _uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));

    // Reset modal state fully on close so re-open is always clean
    document.getElementById('uploadModal').addEventListener('hidden.bs.modal', resetModal);

    document.getElementById('batch-selector').addEventListener('change', e => {
        if (e.target.value) loadBatchData(e.target.value);
    });

    document.getElementById('uploadForm').addEventListener('submit', handleUpload);

    loadBatches();
});

// ── Timeline Status ───────────────────────────────────────────────────────────
function loadTimelineStatus(batchId, period) {
    apiFetch(`${API_BASE}/batch/${batchId}/timeline-status`)
        .then(data => updateTimeline(data, period))
        .catch(() => updateTimeline({ total: 0, pending: 0, generated: 0, verified: 0, overdue: 0 }, period));
}

function updateTimeline(data, period) {
    document.getElementById('tl-total').textContent     = data.total     ?? 0;
    document.getElementById('tl-pending').textContent   = data.pending   ?? 0;
    document.getElementById('tl-generated').textContent = data.generated ?? 0;
    document.getElementById('tl-verified').textContent  = data.verified  ?? 0;
    document.getElementById('tl-overdue').textContent   = data.overdue   ?? 0;
    if (period) document.getElementById('timeline-period').textContent = period;
}

// ── Batch loading ─────────────────────────────────────────────────────────────
function loadBatches() {
    apiFetch(`${API_BASE}/manual-batches`)
        .then(batches => {
            const selector = document.getElementById('batch-selector');
            selector.innerHTML = '<option value="">Select a batch...</option>';
            batches.forEach(batch => {
                const opt = document.createElement('option');
                opt.value = batch.batch_id;
                opt.textContent = `${batch.month}/${batch.year} — ${batch.total_tasks} tasks`;
                selector.appendChild(opt);
            });
            if (batches.length > 0) {
                selector.value = batches[0].batch_id;
                loadBatchData(batches[0].batch_id);
            }
        })
        .catch(err => showGlobalError('Failed to load batches: ' + err.message));
}

function loadBatchData(batchId) {
    if (!batchId) return;
    currentBatchId = batchId;
    document.getElementById('current-batch-period').textContent = 'Loading...';

    Promise.all([
        apiFetch(`${API_BASE}/manual-batch/${batchId}/summary`),
        apiFetch(`${API_BASE}/manual-batch/${batchId}`),
    ])
    .then(([summary, envelope]) => {
        updateSummary(summary);
        updateTable(envelope.items ?? []);
        loadTimelineStatus(batchId, `${summary.month}/${summary.year}`);
    })
    .catch(err => showGlobalError('Failed to load batch: ' + err.message));
}

// ── Summary ───────────────────────────────────────────────────────────────────
function updateSummary(s) {
    document.getElementById('current-batch-period').textContent = `${s.month}/${s.year}`;
    document.getElementById('stat-total').textContent     = s.total;
    document.getElementById('stat-completed').textContent = s.completed;
    document.getElementById('stat-pending').textContent   = s.pending;
    document.getElementById('stat-skipped').textContent   = s.skipped;
    document.getElementById('progress-percentage').textContent = `${s.percentage}%`;
    const bar = document.getElementById('progress-bar');
    bar.style.width = `${s.percentage}%`;
    bar.setAttribute('aria-valuenow', s.percentage);
    document.getElementById('progress-text').textContent = `${s.completed} of ${s.total}`;
}

// Locally adjust summary counters after a single action — avoids a round-trip.
function adjustSummary(fromStatus, toStatus) {
    const dec = id => { const el = document.getElementById(id); el.textContent = Math.max(0, +el.textContent - 1); };
    const inc = id => { const el = document.getElementById(id); el.textContent = +el.textContent + 1; };
    const map = { pending: 'stat-pending', completed: 'stat-completed', skipped: 'stat-skipped' };
    if (map[fromStatus]) dec(map[fromStatus]);
    if (map[toStatus])   inc(map[toStatus]);
    // Recalculate progress from updated DOM values
    const total     = +document.getElementById('stat-total').textContent;
    const completed = +document.getElementById('stat-completed').textContent;
    const pct       = total > 0 ? Math.round((completed / total) * 100) : 0;
    document.getElementById('progress-percentage').textContent = `${pct}%`;
    const bar = document.getElementById('progress-bar');
    bar.style.width = `${pct}%`;
    bar.setAttribute('aria-valuenow', pct);
    document.getElementById('progress-text').textContent = `${completed} of ${total}`;
}

// ── Table rendering ───────────────────────────────────────────────────────────
function updateTable(items) {
    const tbody = document.getElementById('compliance-table-body');
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No compliance tasks found</td></tr>';
        return;
    }
    tbody.innerHTML = items.map(item => buildRow(item)).join('');
}

function buildRow(item) {
    const docCell = item.document_path
        ? `<a href="${API_BASE}/manual-item/${item.item_id}/document" target="_blank"
              class="text-decoration-none" aria-label="View document for ${esc(item.compliance_name)}">📄 View</a>`
        : '<span class="text-muted">—</span>';

    const actionCell = buildActionCell(item.item_id, item.status);

    return `<tr data-item-id="${item.item_id}">
        <td class="ps-4">${esc(item.compliance_name)}</td>
        <td>${esc(item.act_name)}</td>
        <td><span class="badge ${getStatusBadgeClass(item.status)}">${getStatusLabel(item.status)}</span></td>
        <td class="doc-cell">${docCell}</td>
        <td class="text-end pe-4 action-cell">${actionCell}</td>
    </tr>`;
}

function buildActionCell(itemId, status) {
    if (status !== 'pending') return '<span class="text-muted small">—</span>';
    return `<button class="btn btn-sm btn-success me-1"
                    data-action="yes" data-item-id="${itemId}"
                    aria-label="Upload document">YES</button>
            <button class="btn btn-sm btn-outline-danger"
                    data-action="no" data-item-id="${itemId}"
                    aria-label="Skip compliance">NO</button>`;
}

// ── Delegated click handler (single listener, no inline onclick) ──────────────
document.addEventListener('click', e => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const itemId = +btn.dataset.itemId;
    if (btn.dataset.action === 'yes') openUploadModal(itemId);
    if (btn.dataset.action === 'no')  skipCompliance(itemId, btn);
});

// ── Upload ────────────────────────────────────────────────────────────────────
function openUploadModal(itemId) {
    document.getElementById('upload-item-id').value = itemId;
    _uploadModal.show();
}

function resetModal() {
    document.getElementById('upload-file').value = '';
    document.getElementById('upload-error').textContent = '';
    const btn = document.getElementById('upload-submit-btn');
    btn.disabled = false;
    btn.textContent = 'Upload';
}

async function handleUpload(e) {
    e.preventDefault();
    const itemId = +document.getElementById('upload-item-id').value;

    if (_inFlight.has(itemId)) return; // duplicate guard

    const file = document.getElementById('upload-file').files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        document.getElementById('upload-error').textContent = 'File exceeds 5 MB limit.';
        return;
    }

    const btn = document.getElementById('upload-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Uploading…';
    document.getElementById('upload-error').textContent = '';
    _inFlight.add(itemId);

    const formData = new FormData(e.target);

    try {
        const data = await apiFetch(`${API_BASE}/manual-item/upload`, { method: 'POST', body: formData });
        _uploadModal.hide(); // triggers hidden.bs.modal → resetModal()
        updateRow(itemId, 'completed', data.document_path, data.file_size);
        adjustSummary('pending', 'completed');
    } catch (err) {
        document.getElementById('upload-error').textContent = err.message;
        btn.disabled = false;
        btn.textContent = 'Upload';
    } finally {
        _inFlight.delete(itemId);
    }
}

// ── Skip ──────────────────────────────────────────────────────────────────────
async function skipCompliance(itemId, btn) {
    if (_inFlight.has(itemId)) return;

    const prevText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '…';
    _inFlight.add(itemId);

    try {
        await apiFetch(`${API_BASE}/manual-item/skip`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId }),
        });
        updateRow(itemId, 'skipped', null, null);
        adjustSummary('pending', 'skipped');
    } catch (err) {
        showRowError(itemId, err.message);
        btn.disabled = false;
        btn.textContent = prevText;
    } finally {
        _inFlight.delete(itemId);
    }
}

// ── DOM update helpers ────────────────────────────────────────────────────────
function updateRow(itemId, status, docPath, fileSize) {
    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (!row) return;

    row.querySelector('.action-cell').innerHTML = '<span class="text-muted small">—</span>';
    row.querySelector('td:nth-child(3)').innerHTML =
        `<span class="badge ${getStatusBadgeClass(status)}">${getStatusLabel(status)}</span>`;

    if (docPath) {
        const sizeLabel = fileSize ? ` <small class="text-muted">(${(fileSize / 1024).toFixed(1)} KB)</small>` : '';
        row.querySelector('.doc-cell').innerHTML =
            `<a href="${API_BASE}/manual-item/${itemId}/document" target="_blank"
                class="text-decoration-none">📄 View${sizeLabel}</a>`;
    }
}

function showRowError(itemId, msg) {
    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (!row) return;
    const cell = row.querySelector('.action-cell');
    if (!cell.querySelector('.row-error')) {
        const div = document.createElement('div');
        div.className = 'row-error text-danger small mt-1';
        div.setAttribute('role', 'alert');
        div.textContent = msg;
        cell.appendChild(div);
    }
}

function showGlobalError(msg) {
    console.error(msg);
    // Surface in the batch period label so it's always visible
    const el = document.getElementById('current-batch-period');
    el.textContent = '⚠ ' + msg;
    el.classList.add('text-danger');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function getStatusBadgeClass(status) {
    return { completed: 'bg-success', pending: 'bg-warning text-dark', skipped: 'bg-secondary' }[status] ?? 'bg-light text-dark';
}

function getStatusLabel(status) {
    return { completed: '✓ Completed', pending: '⏳ Pending', skipped: '⊘ Skipped' }[status] ?? esc(status);
}
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
.progress {
    background-color: #e9ecef;
}
</style>
@endpush
@endsection
