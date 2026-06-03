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

            {{-- Period (Month & Year Only) --}}
            <div class="grid-row mb-3">
                <div class="grid-col col-1-2">
                    <div class="form-group">
                        <label class="form-label">Period Month <span class="form-required">*</span></label>
                        <select name="period_month" class="form-input" required>
                            <option value="">-- Select Month --</option>
                            <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>January</option>
                            <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>February</option>
                            <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>March</option>
                            <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>April</option>
                            <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>May</option>
                            <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>June</option>
                            <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>July</option>
                            <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>August</option>
                            <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>September</option>
                            <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>October</option>
                            <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>November</option>
                            <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>December</option>
                        </select>
                    </div>
                </div>
                <div class="grid-col col-1-2">
                    <div class="form-group">
                        <label class="form-label">Period Year <span class="form-required">*</span></label>
                        <select name="period_year" class="form-input" required>
                            <option value="">-- Select Year --</option>
                            @php
                                $currentYear = now()->year;
                                foreach (range($currentYear - 5, $currentYear + 2) as $year) {
                                    $selected = $year == $currentYear ? 'selected' : '';
                                    echo "<option value=\"$year\" $selected>$year</option>";
                                }
                            @endphp
                        </select>
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

        {{-- ── Period Selection for Supplementary ─────────────────────────── --}}
        <div style="background:#f5f5f5;padding:16px;border-radius:6px;margin-bottom:16px;">
            <div style="font-size:12px;color:#595959;font-weight:600;margin-bottom:8px;">📅 Select Period for All Supplementary Uploads:</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <select id="suppPeriodMonth" class="form-input" style="font-size:13px;">
                        <option value="">-- Select Month --</option>
                        <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>January</option>
                        <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>February</option>
                        <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>March</option>
                        <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>April</option>
                        <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>May</option>
                        <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>June</option>
                        <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>July</option>
                        <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>August</option>
                        <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>September</option>
                        <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>October</option>
                        <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>December</option>
                    </select>
                </div>
                <div>
                    <select id="suppPeriodYear" class="form-input" style="font-size:13px;">
                        <option value="">-- Select Year --</option>
                        @php
                            $currentYear = now()->year;
                            foreach (range($currentYear - 5, $currentYear + 2) as $year) {
                                $selected = $year == $currentYear ? 'selected' : '';
                                echo "<option value=\"$year\" $selected>$year</option>";
                            }
                        @endphp
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Upload All Button ─────────────────────────────────────────── --}}
        <div id="uploadAllResult" class="alert mb-3" style="display:none;"></div>
        <div class="flex-start gap-2 mb-3">
            <button type="button" id="uploadAllSuppBtn" class="btn btn-primary">
                ⬆️ Upload All Selected Files
            </button>
            <span id="uploadAllSpinner" style="display:none;">
                <span class="spinner"></span>&nbsp;Processing…
            </span>
        </div>

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
                    <div class="upload-card-head">{{ $ds['icon'] }} {{ $ds['label'] }}</div>
                    <div class="upload-card-body">

                        <div style="font-size:11px;color:#8c8c8c;line-height:1.6;margin-bottom:8px;">
                            <strong style="color:#595959;">Required:</strong>
                            <code style="font-size:11px;">{{ $ds['req'] }}</code><br>
                            <strong style="color:#595959;">Optional:</strong>
                            <code style="font-size:11px;">{{ $ds['opt'] }}</code><br>
                            <strong style="color:#595959;">Feeds:</strong>
                            <span style="color:#1d39c4;">{{ $ds['forms'] }}</span>
                        </div>

                        <div class="form-group" style="margin-bottom:10px;">
                            <label class="form-label" style="font-size:13px;">{{ $ds['type'] }}.csv</label>
                            <input type="file" accept=".csv,.txt"
                                   class="csv-input supp-file-input"
                                   id="supp-file-{{ $ds['type'] }}"
                                   data-type="{{ $ds['type'] }}">
                        </div>

                        <div class="file-status" id="supp-status-{{ $ds['type'] }}"></div>

                        <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap;">
                            <a href="{{ route('compliance.templates.smart.download', $ds['type']) }}?branch_id={{ auth()->user()->branch_id ?? 1 }}"
                               class="sample-btn" download
                               style="font-size:11px;background:#52c41a;border-color:#52c41a;color:white;padding:4px 10px;border-radius:3px;text-decoration:none;display:inline-block;" title="Smart Excel template with auto-fill">
                               📊 Excel
                            </a>
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
const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const fetchCsrf = async () => {
    try {
        const r = await fetch('{{ url("/compliance/csrf-token") }}', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });
        if (r.ok) {
            const d = await r.json();
            if (d.token) {
                document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', d.token);
                return d.token;
            }
        }
    } catch (_) {}
    return getCsrf();
};

const refreshCsrf = () => {
    try {
        const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        if (match) {
            const raw = decodeURIComponent(match[1]);
            if (raw.length < 100) {
                document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', raw);
            }
        }
    } catch (_) {}
};

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

document.querySelectorAll('.csv-input').forEach(input => {
    input.addEventListener('change', function () {
        const cardId   = this.dataset.card;
        if (!cardId) return;
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

document.getElementById('coreUploadForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn     = document.getElementById('coreSubmitBtn');
    const spinner = document.getElementById('coreSpinner');

    btn.disabled          = true;
    spinner.style.display = 'inline-flex';
    document.getElementById('coreResult').style.display = 'none';

    try {
        const token = await fetchCsrf();
        const resp = await fetch('{{ route("data.upload-multi") }}', {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body   : new FormData(this),
        });

        const rawText = await resp.text();
        refreshCsrf();
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

document.getElementById('uploadAllSuppBtn').addEventListener('click', async function () {
    const inputs  = [...document.querySelectorAll('.supp-file-input')].filter(i => i.files.length);
    const month = document.getElementById('suppPeriodMonth').value;
    const year = document.getElementById('suppPeriodYear').value;

    if (!inputs.length) {
        showBanner('uploadAllResult', 'error', '⚠️ Please select at least one supplementary file first.');
        return;
    }

    if (!month || !year) {
        showBanner('uploadAllResult', 'error', '⚠️ Please select both Month and Year.');
        return;
    }

    this.disabled = true;
    document.getElementById('uploadAllSpinner').style.display = 'inline-flex';
    document.getElementById('uploadAllResult').style.display  = 'none';

    let passed = 0, failed = 0;

    for (const input of inputs) {
        const type     = input.dataset.type;
        const statusEl = document.getElementById('supp-status-' + type);
        const card     = document.getElementById('supp-card-' + type);
        const uploadBtn= card.querySelector('.supp-upload-btn');

        statusEl.innerHTML = `<span class="badge badge-info">⏳ Uploading…</span>`;
        if (uploadBtn) { uploadBtn.disabled = true; uploadBtn.textContent = '⏳…'; }

        const fd = new FormData();
        fd.append('file', input.files[0]);
        fd.append('type', type);
        fd.append('period_month', month);
        fd.append('period_year', year);

        try {
            const token   = await fetchCsrf();
            const resp    = await fetch('{{ route("data.upload-supplementary") }}', {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body   : fd,
            });
            const rawText = await resp.text();
            refreshCsrf();
            let json;
            try   { json = JSON.parse(rawText.replace(/^\uFEFF/, '')); }
            catch (_) { json = { status: 'error', message: `Server error (${resp.status})` }; }

            if (json.status === 'success') {
                const skipped = json.records_skipped ?? 0;
                const label   = skipped > 0
                    ? `✅ ${json.records_inserted} inserted, ${skipped} skipped`
                    : `✅ ${json.records_inserted} records`;
                statusEl.innerHTML = `<span class="badge badge-success">${label}</span>`;
                if (json.row_errors?.length) {
                    statusEl.innerHTML += `<div style="font-size:11px;color:#cf1322;margin-top:4px;">${json.row_errors.slice(0,3).map(escHtml).join('<br>')}</div>`;
                }
                card.classList.add('ready');
                passed++;
            } else {
                statusEl.innerHTML = `<span class="badge badge-danger">❌ ${escHtml(json.message)}</span>`;
                failed++;
            }
        } catch (err) {
            statusEl.innerHTML = `<span class="badge badge-danger">❌ ${escHtml(err.message)}</span>`;
            failed++;
        } finally {
            if (uploadBtn) { uploadBtn.disabled = false; uploadBtn.textContent = '⬆ Upload'; }
        }
    }

    document.getElementById('uploadAllSpinner').style.display = 'none';
    this.disabled = false;
    showBanner('uploadAllResult',
        failed === 0 ? 'success' : 'error',
        failed === 0
            ? `<strong>✅ All ${passed} file(s) uploaded successfully.</strong>`
            : `<strong>⚠️ ${passed} succeeded, ${failed} failed.</strong> Check individual cards above.`
    );
});

document.querySelectorAll('.supp-upload-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const type     = this.dataset.type;
        const fileInput= document.getElementById('supp-file-' + type);
        const statusEl = document.getElementById('supp-status-' + type);
        const card     = document.getElementById('supp-card-' + type);
        const month = document.getElementById('suppPeriodMonth').value;
        const year = document.getElementById('suppPeriodYear').value;

        if (!fileInput.files.length) {
            statusEl.innerHTML = `<span class="badge badge-danger">⚠️ Please select a file first</span>`;
            return;
        }

        if (!month || !year) {
            statusEl.innerHTML = `<span class="badge badge-danger">⚠️ Please select Month and Year</span>`;
            return;
        }

        const origText  = this.textContent;
        this.disabled   = true;
        this.textContent= '⏳ Uploading…';
        statusEl.innerHTML = `<span class="badge badge-info">⏳ Uploading…</span>`;

        const fd = new FormData();
        fd.append('file', fileInput.files[0]);
        fd.append('type', type);
        fd.append('period_month', month);
        fd.append('period_year', year);

        try {
            const token = await fetchCsrf();
            const resp = await fetch('{{ route("data.upload-supplementary") }}', {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body   : fd,
            });

            const rawText = await resp.text();
            refreshCsrf();
            let json;
            try   { json = JSON.parse(rawText.replace(/^\uFEFF/, '')); }
            catch (_) {
                statusEl.innerHTML = `<span class="badge badge-danger">❌ Server error (${resp.status})</span>`;
                return;
            }

            if (json.status === 'success') {
                const skipped = json.records_skipped ?? 0;
                const label   = skipped > 0
                    ? `✅ ${json.records_inserted} inserted, ${skipped} skipped`
                    : `✅ ${json.records_inserted} records imported`;
                statusEl.innerHTML = `<span class="badge badge-success">${label}</span>`;
                if (json.row_errors?.length) {
                    statusEl.innerHTML += `<div style="font-size:11px;color:#cf1322;margin-top:4px;">${json.row_errors.slice(0,3).map(escHtml).join('<br>')}</div>`;
                }
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
