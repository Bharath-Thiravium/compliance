<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — Compliance Engine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --sa-primary: #1a1a2e;
            --sa-secondary: #16213e;
            --sa-accent: #e94560;
            --sa-sidebar-w: 240px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f0f2f5; }

        /* ── Sidebar ── */
        .sa-sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sa-sidebar-w); height: 100vh;
            background: var(--sa-primary);
            display: flex; flex-direction: column;
            z-index: 100; overflow-y: auto;
        }
        .sa-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sa-brand-title { color: #fff; font-size: 16px; font-weight: 700; }
        .sa-brand-sub   { color: var(--sa-accent); font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .sa-nav { padding: 12px 0; flex: 1; }
        .sa-nav-label { color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; padding: 12px 20px 6px; }
        .sa-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: rgba(255,255,255,0.65);
            text-decoration: none; font-size: 14px; transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sa-nav a:hover, .sa-nav a.active {
            color: #fff; background: rgba(255,255,255,0.06);
            border-left-color: var(--sa-accent);
        }
        .sa-nav a .icon { font-size: 16px; width: 20px; text-align: center; }
        .sa-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sa-footer a { color: rgba(255,255,255,0.5); font-size: 13px; text-decoration: none; }
        .sa-footer a:hover { color: #fff; }

        /* ── Main ── */
        .sa-main { margin-left: var(--sa-sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .sa-topbar {
            background: #fff; padding: 0 24px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid #e8e8e8;
            position: sticky; top: 0; z-index: 50;
        }
        .sa-topbar-title { font-size: 18px; font-weight: 600; color: #1a1a2e; }
        .sa-topbar-user  { font-size: 13px; color: #666; }
        .sa-content { padding: 24px; flex: 1; }

        /* ── Cards ── */
        .sa-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 20px; }
        .sa-card-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .sa-card-title  { font-size: 15px; font-weight: 600; color: #1a1a2e; }
        .sa-card-body   { padding: 20px; }

        /* ── Stat cards ── */
        .sa-stat { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .sa-stat-value { font-size: 32px; font-weight: 700; color: #1a1a2e; }
        .sa-stat-label { font-size: 13px; color: #888; margin-top: 4px; }
        .sa-stat-icon  { font-size: 28px; }

        /* ── Table ── */
        .sa-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .sa-table thead th { background: #fafafa; padding: 12px 16px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #f0f0f0; }
        .sa-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f5f5f5; color: #333; }
        .sa-table tbody tr:hover { background: #fafafa; }

        /* ── Badges ── */
        .badge-full    { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-minimal { background: #fff7e6; color: #fa8c16; border: 1px solid #ffd591; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }

        /* ── Buttons ── */
        .sa-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .sa-btn-primary { background: #1a1a2e; color: #fff; }
        .sa-btn-primary:hover { background: #16213e; color: #fff; }
        .sa-btn-accent  { background: var(--sa-accent); color: #fff; }
        .sa-btn-accent:hover { background: #c73652; color: #fff; }
        .sa-btn-outline { background: transparent; color: #555; border: 1px solid #d9d9d9; }
        .sa-btn-outline:hover { border-color: #1a1a2e; color: #1a1a2e; }
        .sa-btn-sm { padding: 4px 10px; font-size: 12px; }
        .sa-btn-danger { background: #ff4d4f; color: #fff; }
        .sa-btn-danger:hover { background: #cf1322; color: #fff; }

        /* ── Form ── */
        .sa-form-group { margin-bottom: 18px; }
        .sa-label { display: block; font-size: 13px; font-weight: 500; color: #333; margin-bottom: 6px; }
        .sa-input, .sa-select {
            width: 100%; padding: 9px 12px; border: 1px solid #d9d9d9;
            border-radius: 6px; font-size: 14px; transition: border 0.2s;
        }
        .sa-input:focus, .sa-select:focus { border-color: #1a1a2e; outline: none; box-shadow: 0 0 0 2px rgba(26,26,46,0.08); }

        /* ── Alerts ── */
        .sa-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .sa-alert-success { background: #f6ffed; border: 1px solid #b7eb8f; color: #389e0d; }
        .sa-alert-error   { background: #fff2f0; border: 1px solid #ffccc7; color: #cf1322; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .sa-sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sa-sidebar.open { transform: translateX(0); }
            .sa-main { margin-left: 0; }
            .sa-topbar { padding: 0 16px; }
            .sa-content { padding: 16px; }
            .sa-mobile-toggle { display: flex !important; }
        }
        .sa-mobile-toggle {
            display: none; align-items: center; justify-content: center;
            width: 36px; height: 36px; border: none; background: none;
            font-size: 20px; cursor: pointer; color: #333;
        }
        .sa-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 99;
        }
        .sa-overlay.show { display: block; }
    </style>
    @stack('styles')
</head>
<body>

<div class="sa-overlay" id="saOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sa-sidebar" id="saSidebar">
    <div class="sa-brand">
        <div class="sa-brand-title">🏭 Compliance Engine</div>
        <div class="sa-brand-sub">Super Admin</div>
    </div>
    <nav class="sa-nav">
        <div class="sa-nav-label">Overview</div>
        <a href="{{ route('super-admin.dashboard') }}" class="{{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Dashboard
        </a>

        <div class="sa-nav-label">Management</div>
        <a href="{{ route('super-admin.tenants') }}" class="{{ request()->routeIs('super-admin.tenants*') ? 'active' : '' }}">
            <span class="icon">🏢</span> Tenants
        </a>
        <a href="{{ route('super-admin.users') }}" class="{{ request()->routeIs('super-admin.users*') ? 'active' : '' }}">
            <span class="icon">👥</span> Users
        </a>

        <div class="sa-nav-label">System</div>
        <a href="{{ route('super-admin.analytics') }}" class="{{ request()->routeIs('super-admin.analytics') ? 'active' : '' }}">
            <span class="icon">📈</span> Analytics
        </a>
        <a href="{{ route('super-admin.audit-details') }}" class="{{ request()->routeIs('super-admin.audit-details*') ? 'active' : '' }}">
            <span class="icon">🔍</span> Audit Details
        </a>
        <a href="{{ route('super-admin.audit-failures') }}" class="{{ request()->routeIs('super-admin.audit-failures') ? 'active' : '' }}">
            <span class="icon">⚠️</span> Audit Failures
        </a>
        <a href="{{ route('super-admin.pending-filings') }}" class="{{ request()->routeIs('super-admin.pending-filings') ? 'active' : '' }}">
            <span class="icon">📋</span> Pending Filings
        </a>
        <a href="{{ route('super-admin.form-updates') }}" class="{{ request()->routeIs('super-admin.form-updates') ? 'active' : '' }}">
            <span class="icon">📝</span> Form Updates
        </a>
        <a href="{{ route('super-admin.change-password') }}" class="{{ request()->routeIs('super-admin.change-password') ? 'active' : '' }}">
            <span class="icon">🔒</span> Change Password
        </a>

        <div class="sa-nav-label">Navigation</div>
        <a href="{{ route('compliance.dashboard') }}">
            <span class="icon">↩️</span> Back to App
        </a>
    </nav>
    <div class="sa-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
                <a href="#" style="pointer-events:none;">🚪 Logout</a>
            </button>
        </form>
    </div>
</aside>

<!-- Main -->
<div class="sa-main">
    <div class="sa-topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="sa-mobile-toggle" onclick="openSidebar()">☰</button>
            <span class="sa-topbar-title">@yield('page-title', 'Super Admin')</span>
        </div>
        <span class="sa-topbar-user">{{ Auth::user()->name }}</span>
    </div>

    <div class="sa-content">
        @if(session('success'))
            <div class="sa-alert sa-alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="sa-alert sa-alert-error">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="sa-alert sa-alert-error">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openSidebar()  { document.getElementById('saSidebar').classList.add('open'); document.getElementById('saOverlay').classList.add('show'); }
    function closeSidebar() { document.getElementById('saSidebar').classList.remove('open'); document.getElementById('saOverlay').classList.remove('show'); }
</script>
@stack('scripts')
</body>
</html>
