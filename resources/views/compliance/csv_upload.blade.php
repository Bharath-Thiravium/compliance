@extends('compliance.layouts.app')

@section('title', 'Upload Compliance Data')
@section('page-title', 'Upload Compliance Data')

@section('content')

{{-- ── Global result banner ──────────────────────────────────────────────── --}}
<div id="globalResult" class="alert mb-3" style="display:none;"></div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SECTION 1 — CORE DATASETS  (Employees + Payroll + Attendance together)
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="card mb-4">
    <div class="card-header">📂 Core Datasets — Employees, Payroll &amp; Attendance</div>
    <div class="card-body">

        <p style="font-size:13px;color:#595959;margin-bottom:14px;">
            Upload all three core files together. They are validated as a set — employee codes must match across all three files.
        </p>

        <div id="coreResult" class="alert mb-3" style="display:none;"></div>

        <form id="coreUploadForm" enctype="multipart/form-data">
            @csrf

            {{-- Period --}}
            <div class="grid-row mb-3">
                <div class="grid-col col-1-2">
                    <div class="form-group">
                        <label class="form-label">Period From <span class="form-required">*</span></label>
                        <input type="date" name="period_from" class="form-input" required>
                    </div>
                </div>
                <div class="grid-col col-1-2">
                    <div class="form-group">
                        <label class="form-label">Period To <span class="form-required">*</span></label>
                        <input type="date" name="period_to" class="form-input" required>
                    </div>
                </div>
            </div>

            {{-- Three core cards --}}
            <div class="grid-row mb-3">

                @foreach([
                    ['key'=>'employees', 'icon'=>'👥', 'label'=>'Employees CSV',  'req'=>'employee_code, name',                        'opt'=>'designation, department, uan, basic_salary, date_of_joining'],
                    ['key'=>'payroll',   'icon'=>'💰', 'label'=>'Payroll CSV',    'req'=>'employee_code, gross_salary, net_salary',     'opt'=>'basic_salary, hra, pf, esi, professional_tax, salary_month'],
                    ['key'=>'attendance','icon'=>'📅', 'label'=>'Attendance CSV', 'req'=>'employee_code',                              'opt'=>'attendance_date, status, working_hours, overtime_hours'],
                ] as $ds)
                <div class="grid-col col-1-3">
                    <div class="upload-card" id="card-{{ $ds['key'] }}">
                        <div class="upload-card-head">{{ $ds['icon'] }} {{ $ds['label'] }}</div>
                        <div class="upload-card-body">
                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label" style="font-size:13px;">
                                    {{ $ds['key'] }}.csv <span class="form-required">*</span>
                                </label>
                                <input type="file" name="{{ $ds['key'] }}_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-{{ $ds['key'] }}" required>
                            </div>
                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">{{ $ds['req'] }}</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">{{ $ds['opt'] }}</code>
                            </div>
                            <div class="sample-box">
                                <span class="sample-title">📥 Sample CSV</span>
                                <a href="{{ route('csv.template', $ds['key']) }}" class="sample-btn" download>⬇ Download</a>
                            </div>
                            <div class="file-status" id="status-{{ $ds['key'] }}"></div>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            <div class="flex-start gap-2">
                <button type="submit" class="btn btn-primary" id="coreSubmitBtn">
                    ⬆️ Upload Core Datasets
                </button>
                <span id="coreSpinner" style="display:none;">
                    <span class="spinner"></span>&nbsp;Processing…
                </span>
                <a href="{{ route('compliance.dashboard') }}" class="btn">← Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SECTION 2 — SUPPLEMENTARY DATASETS  (each uploaded independently)
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="card mb-4">
    <div class="card-header">📋 Supplementary Datasets — Upload Individually</div>
    <div class="card-body">

        <p style="font-size:13px;color:#595959;margin-bottom:16px;">
            Each supplementary dataset is uploaded independently. Employees must be uploaded first — these datasets reference employee codes already in the system.
        </p>

        <div class="grid-row">

            @php
            $supplementary = [
                ['type'=>'bonus',           'icon'=>'🎁', 'label'=>'Bonus',           'req'=>'employee_code, financial_year, bonus_amount',  'opt'=>'bonus_percentage, payment_date',                    'forms'=>'Form C, Form D'],
                ['type'=>'fines',           'icon'=>'⚠️', 'label'=>'Fines',           'req'=>'employee_code, fine_date, amount',             'opt'=>'fine_reason, showed_cause, heard_by, witness_name', 'forms'=>'Form XX, Shops Fines'],
                ['type'=>'advances',        'icon'=>'💳', 'label'=>'Advances',        'req'=>'employee_code, advance_date, advance_amount',  'opt'=>'purpose, installment_count, monthly_installment',  'forms'=>'Form XXII'],
                ['type'=>'deductions',      'icon'=>'📉', 'label'=>'Deductions',      'req'=>'employee_code, deduction_date, amount',        'opt'=>'deduction_type, damage_particulars',               'forms'=>'Form XX, Form XXI'],
                ['type'=>'incidents',       'icon'=>'🚨', 'label'=>'Incidents',       'req'=>'incident_date',                               'opt'=>'employee_code, location, injury_type, severity',   'forms'=>'Form 11, Form 18, Form 26'],
                ['type'=>'hazard_register', 'icon'=>'☣️', 'label'=>'Hazard Register', 'req'=>'hazard_type, location',                       'opt'=>'risk_rating, control_measure, corrective_action',  'forms'=>'Hazard Register'],
                ['type'=>'contractors',     'icon'=>'🏗️', 'label'=>'Contractors',     'req'=>'contractor_name, license_number',             'opt'=>'nature_of_work, contact_person, mobile, max_workers','forms'=>'Form XII, XIII, XIV, XVI, XVII'],
            ];
            @endphp

            @foreach($supplementary as $ds)
            <div class="grid-col col-1-3" style="margin-bottom:16px;">
                <div class="upload-card" id="supp-card-{{ $ds['type'] }}">
                    <div class="upload-card-head">{{ $ds['icon'] }} {{ $ds['label'] }} CSV</div>
                    <div class="upload-card-body">

                        <div style="font-size:11px;color:#8c8c8c;line-height:1.6;margin-bottom:8px;">
                            <strong style="color:#595959;">Required:</strong>
                            <code style="font-size:11px;">{{ $ds['req'] }}</code><br>
                            <strong style="color:#595959;">Optional:</strong>
                            <code style="font-size:11px;">{{ $ds['opt'] }}</code><br>
                            <strong style="color:#595959;">Feeds:</strong>
                            <span style="color:#1d39c4;">{{ $ds['forms'] }}</span>
                        </div>

                        <div class="form-group" style="margin-bottom:8px;">
                            <input type="file" accept=".csv,.txt"
                                   class="supp-file-input"
                                   id="supp-file-{{ $ds['type'] }}"
                                   data-type="{{ $ds['type'] }}">
                        </div>

                        <div class="file-status" id="supp-status-{{ $ds['type'] }}"></div>

                        <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap;">
                            <a href="{{ route('csv.template', $ds['type']) }}"
                               class="sample-btn" download
                               style="font-size:11px;">⬇ Template</a>
                            <button type="button"
                                    class="btn btn-primary btn-sm supp-upload-btn"
                                    data-type="{{ $ds['type'] }}"
                                    style="font-size:12px;padding:4px 12px;">
                                ⬆ Upload
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Helpers ───────────────────────────────────────────────────────────────────
function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}

function showBanner(elId, type, html) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.className = type === 'success' ? 'alert alert-success mb-3' : 'alert alert-danger mb-3';
    el.innerHTML  = html;
    el.style.display = 'block';
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ── Core file-picker feedback ─────────────────────────────────────────────────
document.querySelectorAll('.csv-input').forEach(input => {
    input.addEventListener('change', function () {
        const cardId   = this.dataset.card;
        const type     = cardId.replace('card-', '');
        const statusEl = document.getElementById('status-' + type);
        const card     = document.getElementById(cardId);
        if (this.files.length) {
            const kb = (this.files[0].size / 1024).toFixed(1);
            statusEl.innerHTML = `<span class="badge badge-success">✓ ${escHtml(this.files[0].name)} (${kb} KB)</span>`;
            card.classList.add('ready');
        } else {
            statusEl.innerHTML = '';
            card.classList.remove('ready');
        }
    });
});

// ── Supplementary file-picker feedback ───────────────────────────────────────
document.querySelectorAll('.supp-file-input').forEach(input => {
    input.addEventListener('change', function () {
        const type     = this.dataset.type;
        const statusEl = document.getElementById('supp-status-' + type);
        const card     = document.getElementById('supp-card-' + type);
        if (this.files.length) {
            const kb = (this.files[0].size / 1024).toFixed(1);
            statusEl.innerHTML = `<span class="badge badge-success">✓ ${escHtml(this.files[0].name)} (${kb} KB)</span>`;
            card.classList.add('ready');
        } else {
            statusEl.innerHTML = '';
            card.classList.remove('ready');
        }
    });
});

// ── Core upload (all 3 together) ──────────────────────────────────────────────
document.getElementById('coreUploadForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn     = document.getElementById('coreSubmitBtn');
    const spinner = document.getElementById('coreSpinner');

    btn.disabled          = true;
    spinner.style.display = 'inline-flex';
    document.getElementById('coreResult').style.display = 'none';

    try {
        const resp = await fetch('{{ route("data.upload-multi") }}', {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body   : new FormData(this),
        });

        const rawText = await resp.text();
        let json;
        try   { json = JSON.parse(rawText.replace(/^\uFEFF/, '')); }
        catch (_) {
            showBanner('coreResult', 'error', `<strong>❌ Server error (${resp.status}):</strong> Unexpected response.`);
            return;
        }

        if (json.status === 'success') {
            const c = json.counts;
            showBanner('coreResult', 'success',
                `<strong>✅ ${escHtml(json.message)}</strong><br>
                 <small>Employees: ${c.employees} &nbsp;|&nbsp; Payroll: ${c.payroll} &nbsp;|&nbsp; Attendance: ${c.attendance}</small>`
            );
            this.reset();
            document.querySelectorAll('.file-status').forEach(el => el.innerHTML = '');
            document.querySelectorAll('.upload-card').forEach(el => el.classList.remove('ready'));
        } else {
            let msg = json.message ?? 'Upload failed.';
            if (json.errors) msg = Object.values(json.errors).flat().join('<br>');
            showBanner('coreResult', 'error', `<strong>❌ Upload failed:</strong><br>${escHtml(msg)}`);
        }
    } catch (err) {
        showBanner('coreResult', 'error', `<strong>❌ Network error:</strong> ${escHtml(err.message)}`);
    } finally {
        btn.disabled          = false;
        spinner.style.display = 'none';
    }
});

// ── Supplementary upload (one at a time) ─────────────────────────────────────
document.querySelectorAll('.supp-upload-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const type     = this.dataset.type;
        const fileInput= document.getElementById('supp-file-' + type);
        const statusEl = document.getElementById('supp-status-' + type);
        const card     = document.getElementById('supp-card-' + type);

        if (!fileInput.files.length) {
            statusEl.innerHTML = `<span class="badge badge-danger">⚠️ Please select a file first</span>`;
            return;
        }

        const origText  = this.textContent;
        this.disabled   = true;
        this.textContent= '⏳ Uploading…';
        statusEl.innerHTML = `<span class="badge badge-info">⏳ Uploading…</span>`;

        const fd = new FormData();
        fd.append('file', fileInput.files[0]);
        fd.append('type', type);

        try {
            const resp = await fetch('{{ route("data.upload-supplementary") }}', {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body   : fd,
            });

            const rawText = await resp.text();
            let json;
            try   { json = JSON.parse(rawText.replace(/^\uFEFF/, '')); }
            catch (_) {
                statusEl.innerHTML = `<span class="badge badge-danger">❌ Server error (${resp.status})</span>`;
                return;
            }

            if (json.status === 'success') {
                statusEl.innerHTML = `<span class="badge badge-success">✅ ${json.records_inserted} records imported</span>`;
                card.classList.add('ready');
                showBanner('globalResult', 'success',
                    `<strong>✅ ${escHtml(json.message)}</strong>`);
            } else {
                statusEl.innerHTML = `<span class="badge badge-danger">❌ ${escHtml(json.message)}</span>`;
                showBanner('globalResult', 'error',
                    `<strong>❌ ${escHtml(type)} upload failed:</strong> ${escHtml(json.message)}`);
            }
        } catch (err) {
            statusEl.innerHTML = `<span class="badge badge-danger">❌ ${escHtml(err.message)}</span>`;
        } finally {
            this.disabled   = false;
            this.textContent= origText;
        }
    });
});
</script>
@endpush
