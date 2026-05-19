@extends('compliance.layouts.antd_base')

@section('title', 'Upload Compliance Data')

@push('styles')
<style>
/* ── Upload cards ─────────────────────────────────────────────────────────── */
.upload-card {
    border: 2px dashed #d9d9d9;
    border-radius: 8px;
    background: #fff;
    transition: border-color 0.2s;
    display: flex;
    flex-direction: column;
}
.upload-card.ready { border-color: #52c41a; }

.upload-card-head {
    background: #8c8c8c;
    color: #fff;
    padding: 10px 16px;
    font-weight: 600;
    font-size: 13px;
}

.upload-card-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.sample-download-box {
    margin-top: 14px !important;
    padding: 12px !important;
    background: #e6fffb !important;
    border: 1px solid #87e8de !important;
    border-radius: 8px !important;
    display: block !important;
    width: 100% !important;
    visibility: visible !important;
    opacity: 1 !important;
    overflow: visible !important;
    position: relative !important;
    z-index: 999 !important;
    box-sizing: border-box !important;
}
.sample-title {
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #08979c !important;
    margin-bottom: 8px !important;
    display: block !important;
}
.sample-download-btn {
    display: block !important;
    width: 100% !important;
    text-align: center !important;
    padding: 8px 12px !important;
    background: #13c2c2 !important;
    color: #fff !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    box-sizing: border-box !important;
}
.sample-download-btn:hover {
    background: #08979c !important;
    color: #fff !important;
    text-decoration: none !important;
}

/* ── File status tag ──────────────────────────────────────────────────────── */
.file-status { min-height: 24px; margin-top: 8px; }
</style>
@endpush

@section('content')

@if ($errors->any())
<div class="ant-alert ant-alert-error mb-3">
    <strong>Upload failed:</strong>
    <ul style="margin:8px 0 0 16px;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="ant-card">
    <div class="ant-card-head">📂 CSV Upload — All 3 Datasets</div>
    <div class="ant-card-body">

        <div id="uploadResult" class="ant-alert mb-3" style="display:none;"></div>

        <form id="csvUploadForm" enctype="multipart/form-data">
            @csrf

            {{-- Period row --}}
            <div class="ant-row mb-3">
                <div class="ant-col ant-col-6">
                    <div class="ant-form-item">
                        <label class="ant-form-item-label">Period From <span style="color:#ff4d4f">*</span></label>
                        <input type="date" name="period_from" class="ant-input" required>
                    </div>
                </div>
                <div class="ant-col ant-col-6">
                    <div class="ant-form-item">
                        <label class="ant-form-item-label">Period To <span style="color:#ff4d4f">*</span></label>
                        <input type="date" name="period_to" class="ant-input" required>
                    </div>
                </div>
            </div>

            {{-- ── Three upload cards ──────────────────────────────────────── --}}
            <div class="ant-row mb-3">

                {{-- EMPLOYEES ------------------------------------------------ --}}
                <div class="ant-col ant-col-4">
                    <div class="upload-card" id="card-employees">
                        <div class="upload-card-head">👥 Employees CSV</div>
                        <div class="upload-card-body">

                            {{-- File picker --}}
                            <div class="ant-form-item" style="margin-bottom:10px;">
                                <label class="ant-form-item-label" style="font-size:13px;">
                                    employees.csv <span style="color:#ff4d4f">*</span>
                                </label>
                                <input type="file" name="employees_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-employees" required
                                       style="width:100%;padding:4px 0;font-size:13px;">
                            </div>

                            {{-- Required columns hint --}}
                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">employee_code, name</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">designation, department, uan, basic_salary, date_of_joining</code>
                            </div>

                            <div class="sample-download-box">
                                <span class="sample-title">📥 Sample CSV Format</span>
                                <a href="/compliance/csv-template/employees"
                                   class="sample-download-btn" download>⬇ Download Template</a>
                            </div>

                            <div class="file-status" id="status-employees"></div>
                        </div>
                    </div>
                </div>

                {{-- PAYROLL -------------------------------------------------- --}}
                <div class="ant-col ant-col-4">
                    <div class="upload-card" id="card-payroll">
                        <div class="upload-card-head">💰 Payroll CSV</div>
                        <div class="upload-card-body">

                            <div class="ant-form-item" style="margin-bottom:10px;">
                                <label class="ant-form-item-label" style="font-size:13px;">
                                    payroll.csv <span style="color:#ff4d4f">*</span>
                                </label>
                                <input type="file" name="payroll_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-payroll" required
                                       style="width:100%;padding:4px 0;font-size:13px;">
                            </div>

                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">employee_code, gross_salary, net_salary</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">basic_salary, hra, pf, esi, professional_tax, salary_month</code>
                            </div>

                            <div class="sample-download-box">
                                <span class="sample-title">📥 Sample CSV Format</span>
                                <a href="/compliance/csv-template/payroll"
                                   class="sample-download-btn" download>⬇ Download Template</a>
                            </div>

                            <div class="file-status" id="status-payroll"></div>
                        </div>
                    </div>
                </div>

                {{-- ATTENDANCE ----------------------------------------------- --}}
                <div class="ant-col ant-col-4">
                    <div class="upload-card" id="card-attendance">
                        <div class="upload-card-head">📅 Attendance CSV</div>
                        <div class="upload-card-body">

                            <div class="ant-form-item" style="margin-bottom:10px;">
                                <label class="ant-form-item-label" style="font-size:13px;">
                                    attendance.csv <span style="color:#ff4d4f">*</span>
                                </label>
                                <input type="file" name="attendance_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-attendance" required
                                       style="width:100%;padding:4px 0;font-size:13px;">
                            </div>

                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">employee_code, working_days</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">present_days, absent_days, weekly_off, paid_leave, ot_hours</code>
                            </div>

                            <div class="sample-download-box">
                                <span class="sample-title">📥 Sample CSV Format</span>
                                <a href="/compliance/csv-template/attendance"
                                   class="sample-download-btn" download>⬇ Download Template</a>
                            </div>

                            <div class="file-status" id="status-attendance"></div>
                        </div>
                    </div>
                </div>

            </div>{{-- /ant-row --}}

            {{-- ── Submit row ─────────────────────────────────────────────── --}}
            <div class="d-flex gap-2 align-items-center">
                <button type="submit" class="ant-btn ant-btn-primary" id="submitBtn">
                    ⬆️ Upload All Datasets
                </button>
                <span id="uploadSpinner" style="display:none;">
                    <span class="spinner"></span>&nbsp;Processing…
                </span>
                <a href="{{ route('compliance.dashboard') }}" class="ant-btn">← Back to Dashboard</a>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── File picker feedback ──────────────────────────────────────────────────────
document.querySelectorAll('.csv-input').forEach(function (input) {
    input.addEventListener('change', function () {
        const cardId   = this.dataset.card;
        const type     = cardId.replace('card-', '');
        const statusEl = document.getElementById('status-' + type);
        const card     = document.getElementById(cardId);

        if (this.files.length) {
            const file = this.files[0];
            const kb   = (file.size / 1024).toFixed(1);
            statusEl.innerHTML = `<span class="ant-tag ant-tag-success">✓ ${file.name} (${kb} KB)</span>`;
            card.classList.add('ready');
        } else {
            statusEl.innerHTML = '';
            card.classList.remove('ready');
        }
    });
});

// ── Form submission ───────────────────────────────────────────────────────────
document.getElementById('csvUploadForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn     = document.getElementById('submitBtn');
    const spinner = document.getElementById('uploadSpinner');
    const result  = document.getElementById('uploadResult');

    btn.disabled          = true;
    spinner.style.display = 'inline-flex';
    result.style.display  = 'none';

    try {
        const resp = await fetch('{{ route("data.upload-multi") }}', {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept'      : 'application/json',
            },
            body: new FormData(this),
        });

        let json;
        const rawText = await resp.text();
        try   { json = JSON.parse(rawText); }
        catch (_) {
            result.className = 'ant-alert ant-alert-error mb-3';
            result.innerHTML = `<strong>❌ Server error (${resp.status}):</strong> Unexpected response. Check server logs.`;
            return;
        }

        if (json.status === 'success') {
            const c = json.counts;
            result.className = 'ant-alert ant-alert-success mb-3';
            result.innerHTML = `
                <strong>✅ ${json.message}</strong><br>
                <small>
                    Employees: ${c.employees} &nbsp;|&nbsp;
                    Payroll: ${c.payroll} &nbsp;|&nbsp;
                    Attendance: ${c.attendance}
                </small>`;
            this.reset();
            document.querySelectorAll('.file-status').forEach(el => el.innerHTML = '');
            document.querySelectorAll('.upload-card').forEach(el => el.classList.remove('ready'));
        } else {
            let msg = json.message ?? 'Upload failed.';
            if (json.errors) {
                msg = Object.values(json.errors).flat().join('<br>');
            }
            result.className = 'ant-alert ant-alert-error mb-3';
            result.innerHTML = `<strong>❌ Upload failed:</strong><br>${msg}`;
        }

    } catch (err) {
        result.className = 'ant-alert ant-alert-error mb-3';
        result.innerHTML = `<strong>❌ Network error:</strong> ${err.message}`;
    } finally {
        result.style.display  = 'block';
        btn.disabled          = false;
        spinner.style.display = 'none';
        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>
@endpush
