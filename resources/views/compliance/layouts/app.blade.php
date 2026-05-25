<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Compliance Engine')</title>
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>

<div class="app-overlay" id="ceOverlay" onclick="closeSidebar()"></div>

<aside class="app-sidebar sidebar-ce" id="ceSidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">🏭</div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">Compliance Engine</div>
            <div class="sidebar-brand-sub">
                @if(isset($subscription)) {{ $subscription }} Plan @else Labour Automation @endif
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Workspace</div>

        <a href="{{ route('compliance.dashboard') }}"
           class="{{ request()->routeIs('compliance.dashboard') ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="{{ route('data.upload-multi.form') }}"
           class="{{ request()->routeIs('data.upload-multi*') ? 'active' : '' }}">
            <span class="nav-icon">📂</span>
            <span class="nav-label">Upload Data</span>
        </a>

        <a href="{{ route('compliance.manual-dashboard') }}"
           class="{{ request()->routeIs('compliance.manual*') ? 'active' : '' }}">
            <span class="nav-icon">📝</span>
            <span class="nav-label">Manual Entry</span>
        </a>

        <div class="sidebar-nav-label">Account</div>

        <a href="{{ route('compliance.settings') }}"
           class="{{ request()->routeIs('compliance.settings*') ? 'active' : '' }}">
            <span class="nav-icon">⚙️</span>
            <span class="nav-label">Settings</span>
        </a>

        @if(Auth::user()->is_super_admin)
            <div class="sidebar-nav-label">Administration</div>
            <a href="{{ route('super-admin.dashboard') }}">
                <span class="nav-icon">👑</span>
                <span class="nav-label">Super Admin</span>
            </a>
        @endif
    </nav>

    {{-- Footer: user + logout --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                <div class="sidebar-user-role">@if(Auth::user()->is_super_admin)Super Admin @elseif(isset($subscription)){{ $subscription }} Plan @else User @endif</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Sign out
            </button>
        </form>
    </div>

</aside>

<div class="app-main">
    <div class="app-topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="mobile-toggle" onclick="openSidebar()">☰</button>
            <span class="topbar-title">@yield('page-title', 'Compliance Engine')</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($subscription))
                <span class="badge {{ $subscription === 'FULL' ? 'badge-success' : 'badge-default' }}">
                    {{ $subscription }}
                </span>
            @endif
            <span class="topbar-user">{{ Auth::user()->email }}</span>
        </div>
    </div>

    <div class="app-content">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">❌ {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openSidebar() {
        document.getElementById('ceSidebar').classList.add('open');
        document.getElementById('ceOverlay').classList.add('show');
    }
    function closeSidebar() {
        document.getElementById('ceSidebar').classList.remove('open');
        document.getElementById('ceOverlay').classList.remove('show');
    }
</script>
@stack('scripts')
</body>
</html>
