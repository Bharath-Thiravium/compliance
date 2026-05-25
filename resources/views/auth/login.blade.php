<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · Compliance Engine</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --brand:#1890ff;--brand-h:#40a9ff;--brand-d:#096dd9;
            --text:#0f172a;--muted:#64748b;--border:#e2e8f0;--ibg:#f8fafc;
            --red:#dc2626;--red-bg:#fef2f2;--red-bd:#fecaca;
            --font:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
        }
        html,body{height:100%;font-family:var(--font)}
        body{
            min-height:100vh;display:flex;
            background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0f172a 100%);
            color:var(--text);
        }
        /* ── Page / Card ── */
        .page{display:flex;width:100%;min-height:100vh;align-items:center;justify-content:center;padding:24px 16px}
        .card{
            display:flex;width:100%;max-width:920px;min-height:560px;
            border-radius:20px;overflow:hidden;
            box-shadow:0 24px 64px rgba(0,0,0,0.45),0 0 0 1px rgba(255,255,255,0.06);
        }
        /* ── Left panel ── */
        .left{
            flex:1.1;background:linear-gradient(160deg,#1e3a5f 0%,#0f172a 100%);
            padding:52px 44px;display:flex;flex-direction:column;justify-content:space-between;
            position:relative;overflow:hidden;
        }
        .left::before{
            content:'';position:absolute;inset:0;
            background-image:linear-gradient(rgba(255,255,255,0.03) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,0.03) 1px,transparent 1px);
            background-size:40px 40px;
        }
        .left::after{
            content:'';position:absolute;width:360px;height:360px;border-radius:50%;
            background:radial-gradient(circle,rgba(24,144,255,0.14) 0%,transparent 70%);
            bottom:-80px;right:-80px;
        }
        .lc{position:relative;z-index:1}
        .logo-row{display:flex;align-items:center;gap:14px;margin-bottom:36px}
        .logo-box{width:52px;height:62px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#fff;border:1px solid rgba(255,255,255,0.2)}
        .logo-box img{width:100%;height:100%;object-fit:cover;display:block}
        .logo-txt{font-size:14px;font-weight:700;color:#fff;line-height:1.3}
        .logo-txt small{display:block;font-size:11px;font-weight:400;color:rgba(255,255,255,0.4)}
        .l-h{font-size:30px;font-weight:800;color:#fff;line-height:1.2;letter-spacing:-.6px;margin-bottom:12px}
        .l-h em{font-style:normal;color:#60a5fa}
        .l-p{font-size:13px;color:rgba(255,255,255,0.5);line-height:1.7;margin-bottom:40px;max-width:280px}
        .feats{display:flex;flex-direction:column;gap:10px}
        .feat{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:10px 14px}
        .fi{width:30px;height:30px;border-radius:7px;flex-shrink:0;background:rgba(24,144,255,0.18);display:flex;align-items:center;justify-content:center}
        .fi svg{width:14px;height:14px;stroke:#93c5fd;fill:none;stroke-width:2}
        .fb strong{display:block;font-size:12px;font-weight:600;color:#fff}
        .fb span{font-size:11px;color:rgba(255,255,255,0.4)}
        .lf{position:relative;z-index:1}
        .lf p{font-size:11px;color:rgba(255,255,255,0.2);margin-top:28px}
        /* ── Right panel ── */
        .right{flex:1;background:#fff;padding:52px 48px;display:flex;flex-direction:column;justify-content:center}
        .fh{margin-bottom:32px}
        .badge{
            display:inline-flex;align-items:center;gap:5px;
            background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;
            border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.4px;
            padding:3px 10px;margin-bottom:14px;text-transform:uppercase;
        }
        .badge svg{width:11px;height:11px;fill:none;stroke:currentColor;stroke-width:2.5}
        .fh h1{font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.5px;margin-bottom:6px}
        .fh p{font-size:13px;color:var(--muted)}
        /* Alert */
        .alert{
            display:flex;align-items:flex-start;gap:9px;padding:11px 14px;border-radius:9px;
            margin-bottom:20px;font-size:13px;line-height:1.5;
            background:var(--red-bg);border:1px solid var(--red-bd);color:var(--red);
        }
        .alert svg{width:14px;height:14px;flex-shrink:0;margin-top:1px;fill:none;stroke:currentColor;stroke-width:2}
        /* Fields */
        .field{margin-bottom:18px}
        .field label{display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px}
        .ib{position:relative}
        .ib svg.ic{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:#94a3b8;fill:none;stroke-width:2;pointer-events:none}
        .ib input{
            width:100%;height:44px;padding:0 40px 0 38px;
            border:1.5px solid var(--border);border-radius:9px;
            font-size:14px;font-family:var(--font);color:var(--text);background:var(--ibg);
            outline:none;transition:border-color .15s,box-shadow .15s;
        }
        .ib input::placeholder{color:#cbd5e1}
        .ib input:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(24,144,255,.12);background:#fff}
        .ib input.err{border-color:var(--red);box-shadow:0 0 0 3px rgba(220,38,38,.1)}
        .eye{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:3px;color:#94a3b8;display:flex;align-items:center;transition:color .15s}
        .eye:hover{color:var(--text)}
        .eye svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2}
        /* Remember */
        .rem{display:flex;align-items:center;gap:7px;margin-bottom:22px;font-size:13px;color:var(--muted);cursor:pointer;user-select:none}
        .rem input{width:15px;height:15px;accent-color:var(--brand);cursor:pointer}
        /* Submit */
        .btn{
            width:100%;height:46px;background:var(--brand);color:#fff;
            font-size:14px;font-weight:700;font-family:var(--font);
            border:none;border-radius:10px;cursor:pointer;
            display:flex;align-items:center;justify-content:center;gap:7px;
            box-shadow:0 4px 14px rgba(24,144,255,.35);
            transition:background .15s,box-shadow .15s,transform .1s;
        }
        .btn:hover{background:var(--brand-h);box-shadow:0 6px 20px rgba(24,144,255,.4)}
        .btn:active{transform:scale(.99)}
        .btn svg{width:17px;height:17px;fill:none;stroke:#fff;stroke-width:2.2}
        .foot{margin-top:24px;text-align:center;font-size:12px;color:#94a3b8}
        @media(max-width:680px){
            .left{display:none}
            .right{padding:40px 28px}
            .card{max-width:420px;border-radius:16px}
        }
    </style>
</head>
<body>
<div class="page">
  <div class="card">

    <div class="left">
      <div class="lc">
        <div class="logo-row">
          <div class="logo-box">
            <img src="{{ asset('images/india-map.jpg') }}" alt="India Compliance">
          </div>
          <div class="logo-txt">Compliance Engine<small>Labour Automation Platform</small></div>
        </div>
        <h2 class="l-h">Statutory Compliance,<br><em>Fully Automated.</em></h2>
        <p class="l-p">Manage all labour law obligations across branches — CLRA to Factories Act — in one platform.</p>
        <div class="feats">
          <div class="feat">
            <div class="fi"><svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="fb"><strong>34 Compliance Forms</strong><span>CLRA · Factories · ESI · EPF · Shops</span></div>
          </div>
          <div class="feat">
            <div class="fi"><svg viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
            <div class="fb"><strong>Multi-Tenant Secure</strong><span>Strict branch & tenant isolation</span></div>
          </div>
          <div class="feat">
            <div class="fi"><svg viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
            <div class="fb"><strong>One-Click PDF Reports</strong><span>Auto-generated, print-ready forms</span></div>
          </div>
        </div>
      </div>
      <div class="lf"><p>© {{ date('Y') }} Compliance Engine · All rights reserved</p></div>
    </div>

    <div class="right">
      <div class="fh">
        <div class="badge">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Secure Access
        </div>
        <h1>Welcome back</h1>
        <p>Sign in to your account to continue.</p>
      </div>

      @if(session('error'))
        <div class="alert">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif
      @if($errors->any())
        <div class="alert" role="alert">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}" novalidate>
        @csrf
        <div class="field">
          <label for="email">Email address</label>
          <div class="ib">
            <input type="email" id="email" name="email" value="{{ old('email') }}"
              placeholder="you@company.com" autocomplete="username" inputmode="email"
              required autofocus class="{{ $errors->has('email') ? 'err' : '' }}">
            <svg class="ic" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="ib">
            <input type="password" id="password" name="password"
              placeholder="••••••••" autocomplete="current-password"
              required class="{{ $errors->has('password') ? 'err' : '' }}">
            <svg class="ic" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <button type="button" class="eye" onclick="togglePw()" aria-label="Toggle password">
              <svg id="eico" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
        <label class="rem" for="remember">
          <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
          Remember me for 30 days
        </label>
        <button type="submit" class="btn">
          <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
          Sign in
        </button>
      </form>
      <p class="foot">Having trouble? Contact your system administrator.</p>
    </div>

  </div>
</div>
<script>
function togglePw(){
    var i=document.getElementById('password'),e=document.getElementById('eico'),s=i.type==='password';
    i.type=s?'text':'password';
    e.innerHTML=s?'<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>':'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>
</body>
</html>
