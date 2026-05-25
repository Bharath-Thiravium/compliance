@extends('compliance.layouts.app')

@section('title', 'Manual Compliance Dashboard')
@section('page-title', 'Manual Compliance Dashboard')

@section('content')
<div class="container-fluid">
    <div class="flex-between mb-3">
        <div>
            <h4 class="mb-0">Compliance Execution Dashboard</h4>
            <p class="text-muted mt-1">
                <span id="current-batch-period">Loading...</span>
            </p>
        </div>
        <div>
            <select id="batch-selector" class="form-select batch-selector-width">
                <option value="">Select a batch...</option>
            </select>
        </div>
    </div>

    <div class="grid-row mb-3">
        <div class="grid-col col-1-4">
            <div class="stat-card">
                <div class="flex-between">
                    <div>
                        <p class="text-muted text-sm mb-1">Total Tasks</p>
                        <h3 class="mb-0" id="stat-total">0</h3>
                    </div>
                    <span class="badge badge-info">📋</span>
                </div>
            </div>
        </div>
        <div class="grid-col col-1-4">
            <div class="stat-card">
                <div class="flex-between">
                    <div>
                        <p class="text-muted text-sm mb-1">Completed</p>
                        <h3 class="mb-0 stat-value-success" id="stat-completed">0</h3>
                    </div>
                    <span class="badge badge-success">✓</span>
                </div>
            </div>
        </div>
        <div class="grid-col col-1-4">
            <div class="stat-card">
                <div class="flex-between">
                    <div>
                        <p class="text-muted text-sm mb-1">Pending</p>
                        <h3 class="mb-0 stat-value-warning" id="stat-pending">0</h3>
                    </div>
                    <span class="badge badge-warning">⏳</span>
                </div>
            </div>
        </div>
        <div class="grid-col col-1-4">
            <div class="stat-card">
                <div class="flex-between">
                    <div>
                        <p class="text-muted text-sm mb-1">Skipped</p>
                        <h3 class="mb-0 stat-value-muted" id="stat-skipped">0</h3>
                    </div>
                    <span class="badge badge-default">⊘</span>
                </div>
            </div>
        </div>
    </div>

    @include('compliance.partials.timeline-status', ['timeline' => []])

    <div class="card mb-3">
        <div class="card-body">
            <div class="flex-between mb-2">
                <strong>Completion Progress</strong>
                <span class="badge badge-info" id="progress-percentage">0%</span>
            </div>
            <div class="progress">
                <div id="progress-bar" class="progress-bar" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span id="progress-text">0 of 0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Compliance Tasks</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="data-table mb-0">
                    <thead>
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
                            <td colspan="5" class="text-center text-muted py-4">
                                Select a batch to view compliance tasks
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
                    <div class="form-group">
                        <label for="upload-file" class="form-label">Select File</label>
                        <input type="file" class="form-input" id="upload-file" name="file"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Accepted: PDF, JPG, PNG (Max 5MB)</small>
                        <div id="upload-error" class="field-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="upload-submit-btn" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const API_BASE = '{{ rtrim(config("app.url"), "/") }}/compliance';
let currentBatchId = null;
let _uploadModal = null;
const _inFlight = new Set();

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) throw new Error('CSRF meta tag missing from layout.');
    return meta.content;
}

const esc = s => String(s ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

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
        console.error('Non-JSON response from', url, '\n', text.slice(0, 300));
        throw new Error(`Server returned an unexpected response (HTTP ${res.status}).`);
    }

    if (!res.ok || data.success === false) {
        throw new Error(data.message || `Request failed (HTTP ${res.status}).`);
    }
    return data;
}

document.addEventListener('DOMContentLoaded', () => {
    _uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
    document.getElementById('uploadModal').addEventListener('hidden.bs.modal', resetModal);
    document.getElementById('batch-selector').addEventListener('change', e => {
        if (e.target.value) loadBatchData(e.target.value);
    });
    document.getElementById('uploadForm').addEventListener('submit', handleUpload);
    loadBatches();
});

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

function adjustSummary(fromStatus, toStatus) {
    const dec = id => { const el = document.getElementById(id); el.textContent = Math.max(0, +el.textContent - 1); };
    const inc = id => { const el = document.getElementById(id); el.textContent = +el.textContent + 1; };
    const map = { pending: 'stat-pending', completed: 'stat-completed', skipped: 'stat-skipped' };
    if (map[fromStatus]) dec(map[fromStatus]);
    if (map[toStatus])   inc(map[toStatus]);
    const total     = +document.getElementById('stat-total').textContent;
    const completed = +document.getElementById('stat-completed').textContent;
    const pct       = total > 0 ? Math.round((completed / total) * 100) : 0;
    document.getElementById('progress-percentage').textContent = `${pct}%`;
    const bar = document.getElementById('progress-bar');
    bar.style.width = `${pct}%`;
    bar.setAttribute('aria-valuenow', pct);
    document.getElementById('progress-text').textContent = `${completed} of ${total}`;
}

function updateTable(items) {
    const tbody = document.getElementById('compliance-table-body');
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No compliance tasks found</td></tr>';
        return;
    }
    tbody.innerHTML = items.map(item => buildRow(item)).join('');
}

function buildRow(item) {
    const docCell = item.document_path
        ? `<a href="${API_BASE}/manual-item/${item.item_id}/document" target="_blank" class="text-decoration-none">📄 View</a>`
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
    if (status !== 'pending') return '<span class="text-muted text-sm">—</span>';
    return `<button class="btn btn-success btn-sm me-1" data-action="yes" data-item-id="${itemId}">YES</button>
            <button class="btn btn-danger btn-sm" data-action="no" data-item-id="${itemId}">NO</button>`;
}

document.addEventListener('click', e => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const itemId = +btn.dataset.itemId;
    if (btn.dataset.action === 'yes') openUploadModal(itemId);
    if (btn.dataset.action === 'no')  skipCompliance(itemId, btn);
});

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
    if (_inFlight.has(itemId)) return;
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
        _uploadModal.hide();
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

function updateRow(itemId, status, docPath, fileSize) {
    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (!row) return;
    row.querySelector('.action-cell').innerHTML = '<span class="text-muted text-sm">—</span>';
    row.querySelector('td:nth-child(3)').innerHTML =
        `<span class="badge ${getStatusBadgeClass(status)}">${getStatusLabel(status)}</span>`;
    if (docPath) {
        const sizeLabel = fileSize ? ` <small class="text-muted">(${(fileSize / 1024).toFixed(1)} KB)</small>` : '';
        row.querySelector('.doc-cell').innerHTML =
            `<a href="${API_BASE}/manual-item/${itemId}/document" target="_blank" class="text-decoration-none">📄 View${sizeLabel}</a>`;
    }
}

function showRowError(itemId, msg) {
    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (!row) return;
    const cell = row.querySelector('.action-cell');
    if (!cell.querySelector('.row-error')) {
        const div = document.createElement('div');
        div.className = 'row-error text-sm mt-1 field-error';
        div.textContent = msg;
        cell.appendChild(div);
    }
}

function showGlobalError(msg) {
    console.error(msg);
    const el = document.getElementById('current-batch-period');
    el.textContent = '⚠ ' + msg;
    el.style.color = 'var(--color-danger)';
}

function getStatusBadgeClass(status) {
    return { completed: 'badge-success', pending: 'badge-warning', skipped: 'badge-default' }[status] ?? 'badge-default';
}

function getStatusLabel(status) {
    return { completed: '✓ Completed', pending: '⏳ Pending', skipped: '⊘ Skipped' }[status] ?? esc(status);
}
</script>
@endpush

@endsection
