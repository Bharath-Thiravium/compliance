@extends('compliance.layouts.app')

@section('title', 'Upload Compliance Data')
@section('page-title', 'Upload Compliance Data')

@section('content')

@if ($errors->any())
<div class="alert alert-danger mb-3">
    <strong>Upload failed:</strong>
    <ul style="margin:8px 0 0 16px;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header">📂 CSV Upload — All 3 Datasets</div>
    <div class="card-body">

        <div id="uploadResult" class="alert mb-3" style="display:none;"></div>

        <form id="csvUploadForm" enctype="multipart/form-data">
            @csrf

            {{-- Period row --}}
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

            {{-- ── Three upload cards ──────────────────────────────────────── --}}
            <div class="grid-row mb-3">

                {{-- EMPLOYEES ------------------------------------------------ --}}
                <div class="grid-col col-1-3">
                    <div class="upload-card" id="card-employees">
                        <div class="upload-card-head">👥 Employees CSV</div>
                        <div class="upload-card-body">

                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label" style="font-size:13px;">
                                    employees.csv <span class="form-required">*</span>
                                </label>
                                <input type="file" name="employees_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-employees" required>
                            </div>

                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">employee_code, name</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">designation, department, uan, basic_salary, date_of_joining</code>
                            </div>

                            <div class="sample-box">
                                <span class="sample-title">📥 Sample CSV Format</span>
                                <a href="{{ route('csv.template.employees') }}"
                                   class="sample-btn" download>⬇ Download Template</a>
                            </div>

                            <div class="file-status" id="status-employees"></div>
                        </div>
                    </div>
                </div>

                {{-- PAYROLL -------------------------------------------------- --}}
                <div class="grid-col col-1-3">
                    <div class="upload-card" id="card-payroll">
                        <div class="upload-card-head">💰 Payroll CSV</div>
                        <div class="upload-card-body">

                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label" style="font-size:13px;">
                                    payroll.csv <span class="form-required">*</span>
                                </label>
                                <input type="file" name="payroll_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-payroll" required>
                            </div>

                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">employee_code, gross_salary, net_salary</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">basic_salary, hra, pf, esi, professional_tax, salary_month</code>
                            </div>

                            <div class="sample-box">
                                <span class="sample-title">📥 Sample CSV Format</span>
                                <a href="{{ route('csv.template.payroll') }}"
                                   class="sample-btn" download>⬇ Download Template</a>
                            </div>

                            <div class="file-status" id="status-payroll"></div>
                        </div>
                    </div>
                </div>

                {{-- ATTENDANCE ----------------------------------------------- --}}
                <div class="grid-col col-1-3">
                    <div class="upload-card" id="card-attendance">
                        <div class="upload-card-head">📅 Attendance CSV</div>
                        <div class="upload-card-body">

                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label" style="font-size:13px;">
                                    attendance.csv <span class="form-required">*</span>
                                </label>
                                <input type="file" name="attendance_file" accept=".csv,.txt"
                                       class="csv-input" data-card="card-attendance" required>
                            </div>

                            <div style="font-size:11px;color:#8c8c8c;line-height:1.7;margin-bottom:4px;">
                                <strong style="color:#595959;">Required:</strong>
                                <code style="font-size:11px;">employee_code, working_days</code><br>
                                <strong style="color:#595959;">Optional:</strong>
                                <code style="font-size:11px;">present_days, absent_days, weekly_off, paid_leave, ot_hours</code>
                            </div>

                            <div class="sample-box">
                                <span class="sample-title">📥 Sample CSV Format</span>
                                <a href="{{ route('csv.template.attendance') }}"
                                   class="sample-btn" download>⬇ Download Template</a>
                            </div>

                            <div class="file-status" id="status-attendance"></div>
                        </div>
                    </div>
                </div>

            </div>{{-- /grid-row --}}

            {{-- ── Submit row ─────────────────────────────────────────────── --}}
            <div class="flex-start gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    ⬆️ Upload All Datasets
                </button>
                <span id="uploadSpinner" style="display:none;">
                    <span class="spinner"></span>&nbsp;Processing…
                </span>
                <a href="{{ route('compliance.dashboard') }}" class="btn">← Back to Dashboard</a>
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
            statusEl.innerHTML = `<span class="badge badge-success">✓ ${file.name} (${kb} KB)</span>`;
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
            result.className = 'alert alert-danger mb-3';
            result.innerHTML = `<strong>❌ Server error (${resp.status}):</strong> Unexpected response. Check server logs.`;
            return;
        }

        if (json.status === 'success') {
            const c = json.counts;
            result.className = 'alert alert-success mb-3';
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
            result.className = 'alert alert-danger mb-3';
            result.innerHTML = `<strong>❌ Upload failed:</strong><br>${msg}`;
        }

    } catch (err) {
        result.className = 'alert alert-danger mb-3';
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
