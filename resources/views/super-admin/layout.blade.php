<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — Compliance Engine</title>
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>

<div class="app-overlay" id="saOverlay" onclick="closeSidebar()"></div>

<aside class="app-sidebar sidebar-ce sidebar-sa-brand" id="saSidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">👑</div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">Super Admin</div>
            <div class="sidebar-brand-sub">Compliance Engine</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Overview</div>
        <a href="{{ route('super-admin.dashboard') }}" class="{{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
            <span class="nav-icon">📊</span><span class="nav-label">Dashboard</span>
        </a>

        <div class="sidebar-nav-label">Management</div>
        <a href="{{ route('super-admin.tenants') }}" class="{{ request()->routeIs('super-admin.tenants*') ? 'active' : '' }}">
            <span class="nav-icon">🏢</span><span class="nav-label">Tenants</span>
        </a>
        <a href="{{ route('super-admin.users') }}" class="{{ request()->routeIs('super-admin.users*') ? 'active' : '' }}">
            <span class="nav-icon">👥</span><span class="nav-label">Users</span>
        </a>

        <div class="sidebar-nav-label">System</div>
        <a href="{{ route('super-admin.analytics') }}" class="{{ request()->routeIs('super-admin.analytics') ? 'active' : '' }}">
            <span class="nav-icon">📈</span><span class="nav-label">Analytics</span>
        </a>
        <a href="{{ route('super-admin.audit-details') }}" class="{{ request()->routeIs('super-admin.audit-details*') ? 'active' : '' }}">
            <span class="nav-icon">🔍</span><span class="nav-label">Audit Details</span>
        </a>
        <a href="{{ route('super-admin.audit-failures') }}" class="{{ request()->routeIs('super-admin.audit-failures') ? 'active' : '' }}">
            <span class="nav-icon">⚠️</span><span class="nav-label">Audit Failures</span>
        </a>
        <a href="{{ route('super-admin.pending-filings') }}" class="{{ request()->routeIs('super-admin.pending-filings') ? 'active' : '' }}">
            <span class="nav-icon">📋</span><span class="nav-label">Pending Filings</span>
        </a>
        <a href="{{ route('super-admin.form-updates') }}" class="{{ request()->routeIs('super-admin.form-updates') ? 'active' : '' }}">
            <span class="nav-icon">📝</span><span class="nav-label">Form Updates</span>
        </a>
        <a href="{{ route('super-admin.change-password') }}" class="{{ request()->routeIs('super-admin.change-password') ? 'active' : '' }}">
            <span class="nav-icon">🔒</span><span class="nav-label">Change Password</span>
        </a>

        <div class="sidebar-nav-label">Navigation</div>
        <a href="{{ route('compliance.dashboard') }}">
            <span class="nav-icon">↩️</span><span class="nav-label">Back to App</span>
        </a>
    </nav>

    {{-- Footer: user + logout --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                <div class="sidebar-user-role">Super Admin</div>
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
            <span class="topbar-title">@yield('page-title', 'Super Admin')</span>
        </div>
        <div class="d-flex align-items-center gap-2">

            {{-- Form Updates --}}
            <div style="position:relative;">
                <button class="notif-btn" id="formNotifBtn">
                    🔔 Form Updates
                    <span class="notif-badge success" id="formNotifBadge" style="display:none">0</span>
                </button>
                <div class="notif-dropdown" id="formNotifDropdown">
                    <div class="notif-header">Recent Government Form Updates</div>
                    <div id="formNotifList"></div>
                    <div class="notif-footer">
                        <a href="{{ route('super-admin.form-updates') }}">View All Updates →</a>
                    </div>
                </div>
            </div>

            {{-- System Alerts --}}
            <div style="position:relative;">
                <button class="notif-btn" id="errorNotifBtn">
                    🚨 System Alerts
                    <span class="notif-badge" id="errorNotifBadge" style="display:none">0</span>
                </button>
                <div class="notif-dropdown" id="errorNotifDropdown">
                    <div class="notif-header">System Errors / Failed Actions</div>
                    <div id="errorNotifList"></div>
                    <div class="notif-footer">
                        <a href="{{ route('super-admin.audit-failures') }}">View Failure Details →</a>
                    </div>
                </div>
            </div>

            <span class="topbar-user">{{ Auth::user()->name }}</span>
        </div>
    </div>

    <div class="app-content">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openSidebar()  { document.getElementById('saSidebar').classList.add('open');    document.getElementById('saOverlay').classList.add('show'); }
    function closeSidebar() { document.getElementById('saSidebar').classList.remove('open'); document.getElementById('saOverlay').classList.remove('show'); }

    document.addEventListener('DOMContentLoaded', function () {
        const formBtn  = document.getElementById('formNotifBtn');
        const formDrop = document.getElementById('formNotifDropdown');
        const formBadge= document.getElementById('formNotifBadge');
        const formList = document.getElementById('formNotifList');
        const errBtn   = document.getElementById('errorNotifBtn');
        const errDrop  = document.getElementById('errorNotifDropdown');
        const errBadge = document.getElementById('errorNotifBadge');
        const errList  = document.getElementById('errorNotifList');

        formBtn.addEventListener('click', e => { e.stopPropagation(); formDrop.classList.toggle('show'); errDrop.classList.remove('show'); });
        errBtn.addEventListener('click',  e => { e.stopPropagation(); errDrop.classList.toggle('show');  formDrop.classList.remove('show'); });
        document.addEventListener('click', () => { formDrop.classList.remove('show'); errDrop.classList.remove('show'); });

        function fetchFormUpdates() {
            fetch('{{ route("super-admin.notifications.forms") }}')
                .then(r => r.json())
                .then(data => {
                    formBadge.textContent = data.count;
                    formBadge.style.display = data.count > 0 ? 'inline-block' : 'none';
                    formList.innerHTML = data.items.length
                        ? data.items.map(i => `
                            <div class="notif-item">
                                <div class="notif-item-title">${i.form_code}${i.is_new ? ' <span class="badge-new">🆕 NEW</span>' : ''}</div>
                                <div class="notif-item-desc">${i.form_name}</div>
                                <div class="notif-item-desc">Section: ${i.section} | Status: ${i.status}</div>
                                <div class="notif-item-time">Updated ${i.updated_at}</div>
                            </div>`).join('')
                        : '<div class="notif-item"><span class="text-muted">No recent updates</span></div>';
                }).catch(() => {});
        }

        function fetchSystemErrors() {
            fetch('{{ route("super-admin.notifications.errors") }}')
                .then(r => r.json())
                .then(data => {
                    errBadge.textContent = data.count;
                    errBadge.style.display = data.count > 0 ? 'inline-block' : 'none';
                    errList.innerHTML = data.items.length
                        ? data.items.map(i => `
                            <div class="notif-item">
                                <div class="notif-item-title">${i.action_type}</div>
                                <div class="notif-item-desc">Form: ${i.form_code} | Batch: #${i.batch_id}</div>
                                <div class="notif-item-desc">${i.error_message}</div>
                                <div class="notif-item-time">${i.time}</div>
                            </div>`).join('')
                        : '<div class="notif-item"><span class="text-muted">No system errors</span></div>';
                }).catch(() => {});
        }

        fetchFormUpdates();
        fetchSystemErrors();
        setInterval(() => { fetchFormUpdates(); fetchSystemErrors(); }, 30000);
    });
</script>
@stack('scripts')
</body>
</html>
