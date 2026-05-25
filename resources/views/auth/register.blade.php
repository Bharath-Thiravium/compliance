<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account · Compliance Engine</title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --brand:#1890ff;--brand-h:#40a9ff;
            --text:#262626;--muted:#8c8c8c;--border:#d9d9d9;--bg:#f5f5f5;
            --red:#cf1322;--red-bg:#fff2f0;--red-bd:#ffccc7;
            --font:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
        }
        body{
            font-family:var(--font);font-size:14px;color:var(--text);
            background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0f172a 100%);
            min-height:100vh;display:flex;align-items:center;justify-content:center;
            padding:24px 16px;
        }
        .wrap{width:100%;max-width:480px}
        .card{background:#fff;border-radius:16px;box-shadow:0 24px 64px rgba(0,0,0,0.4);padding:40px}
        .head{text-align:center;margin-bottom:28px}
        .head-logo{font-size:28px;margin-bottom:8px}
        .head h1{font-size:22px;font-weight:700;color:var(--text);margin-bottom:4px}
        .head p{font-size:13px;color:var(--muted)}
        /* Alert */
        .alert{background:var(--red-bg);border:1px solid var(--red-bd);color:var(--red);padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:20px}
        /* Form */
        .form-group{margin-bottom:18px}
        .form-label{display:block;font-size:13px;font-weight:500;color:var(--text);margin-bottom:6px}
        .form-input,.form-select{
            width:100%;height:40px;padding:0 12px;
            border:1px solid var(--border);border-radius:6px;
            font-size:14px;font-family:var(--font);color:var(--text);background:#fff;
            outline:none;transition:border-color .15s,box-shadow .15s;
        }
        .form-input:focus,.form-select:focus{border-color:var(--brand);box-shadow:0 0 0 2px rgba(24,144,255,.12)}
        .divider{border:none;border-top:1px solid #f0f0f0;margin:20px 0}
        /* Plan grid */
        .plan-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .plan-card{
            border:2px solid var(--border);border-radius:8px;padding:16px 12px;
            cursor:pointer;transition:border-color .15s;text-align:center;
        }
        .plan-card:hover{border-color:var(--brand)}
        .plan-card input[type="radio"]{display:none}
        .plan-card.selected{border-color:var(--brand);background:#e6f4ff}
        .plan-name{font-weight:600;font-size:15px;color:var(--text);margin-bottom:4px}
        .plan-desc{font-size:12px;color:var(--muted);line-height:1.4}
        .plan-badge{display:inline-block;font-size:11px;padding:2px 8px;border-radius:10px;margin-bottom:6px;font-weight:600}
        .badge-full   {background:#f6ffed;color:#52c41a;border:1px solid #b7eb8f}
        .badge-minimal{background:#fff7e6;color:#fa8c16;border:1px solid #ffd591}
        /* Submit */
        .btn-submit{
            width:100%;height:44px;background:var(--brand);color:#fff;
            font-size:14px;font-weight:600;font-family:var(--font);
            border:none;border-radius:8px;cursor:pointer;margin-top:8px;
            transition:background .15s;
        }
        .btn-submit:hover{background:var(--brand-h)}
        .sign-in{text-align:center;margin-top:20px;font-size:13px;color:var(--muted)}
        .sign-in a{color:var(--brand);font-weight:500}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <div class="head-logo">🏭</div>
                <h1>Compliance Engine</h1>
                <p>Create your organisation account</p>
            </div>

            @if($errors->any())
                <div class="alert">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Company Name <span style="color:var(--red)">*</span></label>
                    <input type="text" class="form-input" name="company_name"
                           value="{{ old('company_name') }}" placeholder="e.g. Acme Industries Pvt Ltd" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Your Name <span style="color:var(--red)">*</span></label>
                    <input type="text" class="form-input" name="name"
                           value="{{ old('name') }}" placeholder="Full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span style="color:var(--red)">*</span></label>
                    <input type="email" class="form-input" name="email"
                           value="{{ old('email') }}" placeholder="admin@company.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password <span style="color:var(--red)">*</span></label>
                    <input type="password" class="form-input" name="password"
                           placeholder="Min. 8 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password <span style="color:var(--red)">*</span></label>
                    <input type="password" class="form-input" name="password_confirmation"
                           placeholder="Re-enter password" required>
                </div>

                <hr class="divider">

                <div class="form-group">
                    <label class="form-label">Subscription Plan</label>
                    <div class="plan-grid" id="planGrid">
                        <label class="plan-card {{ old('subscription_type', 'FULL') === 'FULL' ? 'selected' : '' }}"
                               onclick="selectPlan(this,'FULL')">
                            <input type="radio" name="subscription_type" value="FULL"
                                   {{ old('subscription_type', 'FULL') === 'FULL' ? 'checked' : '' }}>
                            <div class="plan-badge badge-full">FULL</div>
                            <div class="plan-name">Full Access</div>
                            <div class="plan-desc">All 34 forms, auto-generation, bulk automation & digital signatures</div>
                        </label>
                        <label class="plan-card {{ old('subscription_type') === 'MINIMAL' ? 'selected' : '' }}"
                               onclick="selectPlan(this,'MINIMAL')">
                            <input type="radio" name="subscription_type" value="MINIMAL"
                                   {{ old('subscription_type') === 'MINIMAL' ? 'checked' : '' }}>
                            <div class="plan-badge badge-minimal">MINIMAL</div>
                            <div class="plan-name">Minimal</div>
                            <div class="plan-desc">Core forms, manual upload & analysis reports only</div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Create Account</button>
            </form>

            <div class="sign-in">
                Already have an account? <a href="{{ route('login') }}">Sign In</a>
            </div>
        </div>
    </div>
    <script>
        function selectPlan(el, val) {
            document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input[type="radio"]').checked = true;
        }
    </script>
</body>
</html>
