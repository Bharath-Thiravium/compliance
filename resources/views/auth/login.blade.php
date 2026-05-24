<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Sign in · Compliance Engine</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="{{ url('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand:      #0f172a;
            --brand-mid:  #1e3a5f;
            --accent:     #3b82f6;
            --accent-h:   #2563eb;
            --accent-glow:rgba(59,130,246,0.4);
            --white:      #ffffff;
            --text:       #0f172a;
            --text-soft:  #475569;
            --border:     #e2e8f0;
            --input-bg:   #f8fafc;
            --red:        #ef4444;
            --red-bg:     #fef2f2;
            --red-border: #fecaca;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--brand);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ─── Animated mesh background ─── */
        .bg {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(59,130,246,0.22) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(139,92,246,0.18) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 60% 30%, rgba(6,182,212,0.12) 0%, transparent 50%),
                linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
            animation: bgShift 20s ease-in-out infinite alternate;
        }
        @keyframes bgShift {
            0%   { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(15deg); }
        }

        /* Floating orbs */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(100px); pointer-events: none; z-index: 0;
            animation: float 25s ease-in-out infinite alternate;
        }
        .orb-1 { width: 700px; height: 700px; background: rgba(59,130,246,0.12); top: -20%; left: -15%; animation-delay: 0s; }
        .orb-2 { width: 500px; height: 500px; background: rgba(139,92,246,0.10); bottom: -10%; right: -10%; animation-delay: -8s; }
        .orb-3 { width: 300px; height: 300px; background: rgba(6,182,212,0.08); top: 50%; left: 45%; animation-delay: -16s; }
        @keyframes float {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, 20px) scale(1.06); }
        }

        /* ─── Page wrapper ─── */
        .page {
            position: relative; z-index: 1;
            display: flex; width: 100%; min-height: 100vh;
            align-items: center; justify-content: center;
            padding: 32px 20px;
        }

        /* ─── Card ─── */
        .card {
            display: flex;
            width: 100%; max-width: 960px;
            min-height: 580px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06);
        }

        /* ─── Left panel ─── */
        .left {
            flex: 1.1;
            background: linear-gradient(155deg, #1e3a5f 0%, #0f172a 100%);
            padding: 56px 48px;
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }

        /* Grid pattern overlay */
        .left::before {
            content: '';
            position: absolute; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Glowing circle decoration */
        .left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%);
            bottom: -100px; right: -100px; z-index: 0;
        }

        .left-content { position: relative; z-index: 1; }

        .logo-wrap {
            display: flex; align-items: center; gap: 14px; margin-bottom: 40px;
        }
        .logo-img {
            width: 52px; height: 52px; border-radius: 12px;
            overflow: hidden; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        .logo-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .logo-name {
            font-size: 15px; font-weight: 700; color: #fff; letter-spacing: -0.2px;
            line-height: 1.3;
        }
        .logo-name span { display: block; font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.45); letter-spacing: 0.5px; }

        .left-headline {
            font-size: 32px; font-weight: 800; color: #fff;
            line-height: 1.18; letter-spacing: -0.8px; margin-bottom: 14px;
        }
        .left-headline em { font-style: normal; color: #60a5fa; }

        .left-sub {
            font-size: 14px; color: rgba(255,255,255,0.55);
            line-height: 1.7; margin-bottom: 44px; max-width: 300px;
        }

        /* Feature pills */
        .features { display: flex; flex-direction: column; gap: 14px; }
        .feat {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; padding: 12px 16px;
            backdrop-filter: blur(4px);
            transition: background .2s;
        }
        .feat:hover { background: rgba(255,255,255,0.08); }
        .feat-icon {
            width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
            background: rgba(59,130,246,0.2);
            display: flex; align-items: center; justify-content: center;
        }
        .feat-icon svg { width: 16px; height: 16px; stroke: #93c5fd; fill: none; stroke-width: 2; }
        .feat-body strong { display: block; font-size: 13px; font-weight: 600; color: #fff; }
        .feat-body span { font-size: 11px; color: rgba(255,255,255,0.45); }

        .left-footer { position: relative; z-index: 1; }
        .left-footer p { font-size: 11px; color: rgba(255,255,255,0.25); margin-top: 32px; }

        /* ─── Right panel ─── */
        .right {
            flex: 1;
            background: #fff;
            padding: 56px 52px;
            display: flex; flex-direction: column; justify-content: center;
        }

        .form-top { margin-bottom: 36px; }
        .form-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #eff6ff; border: 1px solid #bfdbfe;
            color: var(--accent-h); border-radius: 20px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.5px;
            padding: 4px 12px; margin-bottom: 16px; text-transform: uppercase;
        }
        .form-badge svg { width: 12px; height: 12px; fill: none; stroke: currentColor; stroke-width: 2.5; }
        .form-h1 {
            font-size: 28px; font-weight: 800; color: var(--text);
            letter-spacing: -0.6px; line-height: 1.2; margin-bottom: 8px;
        }
        .form-desc { font-size: 14px; color: var(--text-soft); line-height: 1.6; }

        /* Alert */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 16px; border-radius: 10px; margin-bottom: 22px;
            font-size: 13px; line-height: 1.5;
        }
        .alert-error {
            background: var(--red-bg); border: 1px solid var(--red-border); color: var(--red);
        }
        .alert svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px; fill: none; stroke: currentColor; stroke-width: 2; }

        /* Fields */
        .field { margin-bottom: 20px; }
        .field-label {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 7px;
        }
        .field-label label { font-size: 13px; font-weight: 600; color: var(--text); }

        .input-box { position: relative; }
        .input-box .ico {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 16px; height: 16px; stroke: #94a3b8; fill: none; stroke-width: 2;
            pointer-events: none; transition: stroke .2s;
        }
        .input-box input {
            width: 100%; height: 46px;
            padding: 0 44px 0 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px; font-family: inherit;
            color: var(--text); background: var(--input-bg);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .input-box input::placeholder { color: #cbd5e1; }
        .input-box input:hover { border-color: #94a3b8; }
        .input-box input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3.5px rgba(59,130,246,0.14);
            background: #fff;
        }
        .input-box input:focus + .ico,
        .input-box input:focus ~ .ico { stroke: var(--accent); }
        .input-box input.err {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
        }

        /* Eye toggle */
        .eye-btn {
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; padding: 4px;
            color: #94a3b8; display: flex; align-items: center;
            transition: color .2s; border-radius: 4px;
        }
        .eye-btn:hover { color: var(--text); }
        .eye-btn svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2; }

        /* Remember row */
        .remember-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 26px;
        }
        .check-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-soft); cursor: pointer; user-select: none;
        }
        .check-label input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: var(--accent); cursor: pointer;
            border-radius: 4px;
        }

        /* Submit */
        .btn {
            width: 100%; height: 48px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff; font-size: 15px; font-weight: 700; font-family: inherit;
            border: none; border-radius: 12px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 16px var(--accent-glow);
            transition: transform .15s, box-shadow .2s, filter .2s;
            letter-spacing: 0.1px;
        }
        .btn:hover {
            filter: brightness(1.08);
            box-shadow: 0 8px 24px var(--accent-glow);
            transform: translateY(-1px);
        }
        .btn:active { transform: translateY(0) scale(0.99); }
        .btn svg { width: 18px; height: 18px; fill: none; stroke: #fff; stroke-width: 2.2; }

        /* Divider */
        .sep {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0; color: #cbd5e1; font-size: 12px;
        }
        .sep::before, .sep::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* Footer note */
        .foot { margin-top: 28px; text-align: center; }
        .foot p { font-size: 12px; color: #94a3b8; }

        /* Responsive */
        @media (max-width: 720px) {
            .left { display: none; }
            .right { padding: 44px 32px; }
            .card { max-width: 460px; border-radius: 20px; }
        }
        @media (max-width: 400px) {
            .right { padding: 36px 24px; }
        }
    </style>
</head>
<body>

<div class="bg"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="page">
    <div class="card">

        {{-- ── Left branding ── --}}
        <div class="left">
            <div class="left-content">
                <div class="logo-wrap">
                    <div class="logo-img">
                        <img src="{{ url('images/esi_logo.jpg') }}" alt="Compliance Engine">
                    </div>
                    <div class="logo-name">
                        Compliance Engine
                        <span>Labour Automation Platform</span>
                    </div>
                </div>

                <h2 class="left-headline">
                    Statutory Compliance,<br>
                    <em>Fully Automated.</em>
                </h2>
                <p class="left-sub">
                    Manage all your labour law obligations across branches — from CLRA to Factories Act — in one unified platform.
                </p>

                <div class="features">
                    <div class="feat">
                        <div class="feat-icon">
                            <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="feat-body">
                            <strong>34 Compliance Forms</strong>
                            <span>CLRA · Factories Act · ESI · EPF · Shops</span>
                        </div>
                    </div>
                    <div class="feat">
                        <div class="feat-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div class="feat-body">
                            <strong>Multi-Tenant Secure</strong>
                            <span>Strict branch & tenant data isolation</span>
                        </div>
                    </div>
                    <div class="feat">
                        <div class="feat-icon">
                            <svg viewBox="0 0 24 24"><path d="M7 21h10M12 21V3m0 0L8 7m4-4l4 4"/></svg>
                        </div>
                        <div class="feat-body">
                            <strong>One-Click PDF Reports</strong>
                            <span>Auto-generated, print-ready forms</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="left-footer">
                <p>© {{ date('Y') }} Compliance Engine · All rights reserved</p>
            </div>
        </div>

        {{-- ── Right form ── --}}
        <div class="right">
            <div class="form-top">
                <div class="form-badge">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Secure Access
                </div>
                <h1 class="form-h1">Welcome back</h1>
                <p class="form-desc">Sign in to your account to continue.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error" role="alert" aria-live="polite">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf

                <div class="field">
                    <div class="field-label">
                        <label for="email">Email address</label>
                    </div>
                    <div class="input-box">
                        <input
                            type="email" id="email" name="email"
                            value="{{ old('email') }}"
                            placeholder="you@company.com"
                            autocomplete="username" inputmode="email"
                            required autofocus
                            class="{{ $errors->has('email') ? 'err' : '' }}"
                        >
                        <svg class="ico" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                </div>

                <div class="field">
                    <div class="field-label">
                        <label for="password">Password</label>
                    </div>
                    <div class="input-box">
                        <input
                            type="password" id="password" name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            class="{{ $errors->has('password') ? 'err' : '' }}"
                        >
                        <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <button type="button" class="eye-btn" id="eyeBtn" onclick="togglePw()" aria-label="Show/hide password">
                            <svg id="eyeIco" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="check-label" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Remember me for 30 days
                    </label>
                </div>

                <button type="submit" class="btn">
                    <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Sign in
                </button>
            </form>

            <div class="foot">
                <p>Having trouble? Contact your system administrator.</p>
            </div>
        </div>

    </div>
</div>

<script>
    function togglePw() {
        const inp = document.getElementById('password');
        const ico = document.getElementById('eyeIco');
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        ico.innerHTML = show
            ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
</script>

</body>
</html>
