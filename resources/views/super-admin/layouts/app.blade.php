<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin - Compliance Engine')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/super-admin-responsive.css') }}" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f5f5f5; }
        .ant-layout { min-height: 100vh; }
        .ant-layout-header { background: linear-gradient(135deg, #722ed1 0%, #eb2f96 100%); padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .ant-layout-content { padding: 24px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .ant-card { background: white; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03), 0 1px 6px -1px rgba(0,0,0,0.02), 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 16px; }
        .ant-card-head { background: #722ed1; color: white; border-radius: 8px 8px 0 0; padding: 16px 24px; font-weight: 600; }
        .ant-card-head.success { background: #52c41a; }
        .ant-card-head.warning { background: #faad14; }
        .ant-card-head.danger { background: #ff4d4f; }
        .ant-card-head.info { background: #13c2c2; }
        .ant-card-head.secondary { background: #8c8c8c; }
        .ant-card-body { padding: 24px; }
        .ant-row { display: flex; flex-wrap: wrap; margin: -8px; }
        .ant-col { padding: 8px; }
        .ant-col-6 { flex: 0 0 50%; max-width: 50%; }
        .ant-col-4 { flex: 0 0 33.333%; max-width: 33.333%; }
        .ant-col-3 { flex: 0 0 25%; max-width: 25%; }
        .ant-col-8 { flex: 0 0 66.666%; max-width: 66.666%; }
        .ant-col-12 { flex: 0 0 100%; max-width: 100%; }
        @media (max-width: 768px) { .ant-col-6, .ant-col-4, .ant-col-3, .ant-col-8 { flex: 0 0 100%; max-width: 100%; } }
        .ant-btn { height: 40px; padding: 4px 15px; border-radius: 6px; font-size: 14px; font-weight: 500; border: 1px solid #d9d9d9; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .ant-btn-primary { background: #722ed1; color: white; border-color: #722ed1; }
        .ant-btn-primary:hover { background: #9254de; border-color: #9254de; color: white; }
        .ant-btn-success { background: #52c41a; color: white; border-color: #52c41a; }
        .ant-btn-info { background: #13c2c2; color: white; border-color: #13c2c2; }
        .ant-btn-sm { height: 32px; padding: 0 12px; font-size: 13px; }
        .ant-tag { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: 500; }
        .ant-tag-success { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .ant-tag-warning { background: #fffbe6; color: #faad14; border: 1px solid #ffe58f; }
        .ant-tag-error { background: #fff2f0; color: #ff4d4f; border: 1px solid #ffccc7; }
        .ant-tag-default { background: #fafafa; color: #595959; border: 1px solid #d9d9d9; }
        .ant-tag-info { background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff; }
        .ant-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ant-table thead th { background: #fafafa; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 1px solid #f0f0f0; }
        .ant-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; }
        .ant-table tbody tr:hover { background: #fafafa; }
        .text-center { text-align: center; }
        .text-muted { color: #8c8c8c; }
        .mb-2 { margin-bottom: 8px; }
        .mt-4 { margin-top: 24px; }
        .d-flex { display: flex; }
        .gap-2 { gap: 8px; }
        .header-brand { color: white; font-size: 20px; font-weight: 600; }
        .header-actions { display: flex; align-items: center; gap: 16px; }
        .header-user { color: white; font-size: 14px; }
        .ant-btn-outline { background: transparent; color: white; border-color: white; }
        .ant-btn-outline:hover { background: rgba(255,255,255,0.1); }
        .stat-card { background: white; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
        .stat-card h3 { font-size: 32px; margin: 0; color: #722ed1; }
        .stat-card p { margin: 8px 0 0 0; color: #8c8c8c; font-size: 14px; }
        footer { text-align: center; color: #8c8c8c; padding: 24px; margin-top: 32px; font-size: 13px; }
        
        /* Notification Styles */
        .notification-btn { position: relative; background: transparent; color: white; border: 1px solid rgba(255,255,255,0.3); padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.3s; }
        .notification-btn:hover { background: rgba(255,255,255,0.1); }
        .notification-badge { position: absolute; top: -8px; right: -8px; background: #ff4d4f; color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; font-weight: 600; min-width: 20px; text-align: center; }
        .notification-badge.success { background: #52c41a; }
        .notification-dropdown { position: absolute; top: 50px; right: 0; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 400px; max-height: 500px; overflow-y: auto; z-index: 1000; display: none; }
        .notification-dropdown.show { display: block; }
        .notification-header { padding: 16px; border-bottom: 1px solid #f0f0f0; font-weight: 600; color: #262626; }
        .notification-item { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s; }
        .notification-item:hover { background: #fafafa; }
        .notification-item-title { font-weight: 600; color: #262626; margin-bottom: 4px; }
        .notification-item-desc { font-size: 13px; color: #8c8c8c; margin-bottom: 4px; }
        .notification-item-time { font-size: 12px; color: #bfbfbf; }
        .notification-footer { padding: 12px 16px; text-align: center; border-top: 1px solid #f0f0f0; }
        .notification-footer a { color: #722ed1; text-decoration: none; font-weight: 500; }
        .notification-footer a:hover { color: #9254de; }
        .new-badge { background: #52c41a; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 8px; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="ant-layout">
        <header class="ant-layout-header">
            <span class="header-brand">👑 Super Admin Panel</span>
            <div class="header-actions">
                <a href="{{ route('super-admin.dashboard') }}" class="ant-btn ant-btn-sm ant-btn-outline">Dashboard</a>
                <a href="{{ route('super-admin.audit-details') }}" class="ant-btn ant-btn-sm ant-btn-outline">Audit Details</a>
                
                <!-- Form Updates Notification -->
                <div style="position: relative;">
                    <button class="notification-btn" id="formNotificationBtn">
                        🔔 Form Updates
                        <span class="notification-badge success" id="formNotificationBadge">0</span>
                    </button>
                    <div class="notification-dropdown" id="formNotificationDropdown">
                        <div class="notification-header">Recent Government Form Updates</div>
                        <div id="formNotificationList"></div>
                        <div class="notification-footer">
                            <a href="{{ route('super-admin.form-updates') }}">View All Updates →</a>
                        </div>
                    </div>
                </div>
                
                <!-- System Errors Notification -->
                <div style="position: relative;">
                    <button class="notification-btn" id="errorNotificationBtn">
                        🚨 System Alerts
                        <span class="notification-badge" id="errorNotificationBadge">0</span>
                    </button>
                    <div class="notification-dropdown" id="errorNotificationDropdown">
                        <div class="notification-header">System Errors / Failed Actions</div>
                        <div id="errorNotificationList"></div>
                        <div class="notification-footer">
                            <a href="{{ route('super-admin.audit-failures') }}">View Failure Details →</a>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('super-admin.change-password') }}" class="ant-btn ant-btn-sm ant-btn-outline">Change Password</a>
                <span class="header-user">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="ant-btn ant-btn-sm ant-btn-outline">Logout</button>
                </form>
            </div>
        </header>
        <main class="ant-layout-content">
            @yield('content')
        </main>
        <footer>
            <small>Super Admin Panel | Compliance Engine | Laravel 12</small>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Notification System
        document.addEventListener('DOMContentLoaded', function() {
            const formBtn = document.getElementById('formNotificationBtn');
            const formDropdown = document.getElementById('formNotificationDropdown');
            const formBadge = document.getElementById('formNotificationBadge');
            const formList = document.getElementById('formNotificationList');
            
            const errorBtn = document.getElementById('errorNotificationBtn');
            const errorDropdown = document.getElementById('errorNotificationDropdown');
            const errorBadge = document.getElementById('errorNotificationBadge');
            const errorList = document.getElementById('errorNotificationList');
            
            // Toggle dropdowns
            formBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                formDropdown.classList.toggle('show');
                errorDropdown.classList.remove('show');
            });
            
            errorBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                errorDropdown.classList.toggle('show');
                formDropdown.classList.remove('show');
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                formDropdown.classList.remove('show');
                errorDropdown.classList.remove('show');
            });
            
            // Fetch form updates
            function fetchFormUpdates() {
                fetch('{{ route("super-admin.notifications.forms") }}')
                    .then(response => response.json())
                    .then(data => {
                        formBadge.textContent = data.count;
                        formBadge.style.display = data.count > 0 ? 'block' : 'none';
                        
                        if (data.items.length > 0) {
                            formList.innerHTML = data.items.map(item => `
                                <div class="notification-item">
                                    <div class="notification-item-title">
                                        ${item.form_code}
                                        ${item.is_new ? '<span class="new-badge">🆕 NEW</span>' : ''}
                                    </div>
                                    <div class="notification-item-desc">${item.form_name}</div>
                                    <div class="notification-item-desc">Section: ${item.section} | Status: ${item.status}</div>
                                    <div class="notification-item-time">Updated ${item.updated_at}</div>
                                </div>
                            `).join('');
                        } else {
                            formList.innerHTML = '<div style="padding: 16px; text-align: center; color: #8c8c8c;">No recent updates</div>';
                        }
                    })
                    .catch(error => console.error('Error fetching form updates:', error));
            }
            
            // Fetch system errors
            function fetchSystemErrors() {
                fetch('{{ route("super-admin.notifications.errors") }}')
                    .then(response => response.json())
                    .then(data => {
                        errorBadge.textContent = data.count;
                        errorBadge.style.display = data.count > 0 ? 'block' : 'none';
                        
                        if (data.items.length > 0) {
                            errorList.innerHTML = data.items.map(item => `
                                <div class="notification-item">
                                    <div class="notification-item-title">${item.action_type}</div>
                                    <div class="notification-item-desc">Form: ${item.form_code} | Batch: #${item.batch_id}</div>
                                    <div class="notification-item-desc">${item.error_message}</div>
                                    <div class="notification-item-time">${item.time}</div>
                                </div>
                            `).join('');
                        } else {
                            errorList.innerHTML = '<div style="padding: 16px; text-align: center; color: #8c8c8c;">No system errors</div>';
                        }
                    })
                    .catch(error => console.error('Error fetching system errors:', error));
            }
            
            // Initial fetch
            fetchFormUpdates();
            fetchSystemErrors();
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                fetchFormUpdates();
                fetchSystemErrors();
            }, 30000);
        });
    </script>
    @stack('scripts')
</body>
</html>
