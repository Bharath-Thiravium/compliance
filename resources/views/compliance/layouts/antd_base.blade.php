<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Compliance Engine')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.13.0/reset.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f5f5f5; }
        .ant-layout { min-height: 100vh; }
        .ant-layout-header { background: linear-gradient(135deg, #1890ff 0%, #722ed1 100%); padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .ant-layout-content { padding: 24px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .ant-card { border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03), 0 1px 6px -1px rgba(0,0,0,0.02), 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 16px; }
        .ant-card-head { background: #1890ff; color: white; border-radius: 8px 8px 0 0; padding: 16px 24px; font-weight: 600; }
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
        .ant-col-8 { flex: 0 0 66.666%; max-width: 66.666%; }
        .ant-col-12 { flex: 0 0 100%; max-width: 100%; }
        @media (max-width: 768px) { .ant-col-6, .ant-col-4, .ant-col-8 { flex: 0 0 100%; max-width: 100%; } }
        .ant-btn { height: 40px; padding: 4px 15px; border-radius: 6px; font-size: 14px; font-weight: 500; border: 1px solid #d9d9d9; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .ant-btn-primary { background: #1890ff; color: white; border-color: #1890ff; }
        .ant-btn-primary:hover { background: #40a9ff; border-color: #40a9ff; }
        .ant-btn-success { background: #52c41a; color: white; border-color: #52c41a; }
        .ant-btn-success:hover { background: #73d13d; border-color: #73d13d; }
        .ant-btn-info { background: #13c2c2; color: white; border-color: #13c2c2; }
        .ant-btn-info:hover { background: #36cfc9; border-color: #36cfc9; }
        .ant-btn-warning { background: #faad14; color: white; border-color: #faad14; }
        .ant-btn-warning:hover { background: #ffc53d; border-color: #ffc53d; }
        .ant-btn-sm { height: 32px; padding: 0 12px; font-size: 13px; }
        .ant-input, .ant-select { height: 40px; padding: 4px 11px; border: 1px solid #d9d9d9; border-radius: 6px; width: 100%; font-size: 14px; transition: all 0.3s; }
        .ant-input:focus, .ant-select:focus { border-color: #4096ff; box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.1); outline: none; }
        .ant-form-item { margin-bottom: 16px; }
        .ant-form-item-label { font-weight: 500; color: #262626; margin-bottom: 8px; display: block; font-size: 14px; }
        .ant-tag { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: 500; }
        .ant-tag-success { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .ant-tag-warning { background: #fffbe6; color: #faad14; border: 1px solid #ffe58f; }
        .ant-tag-error { background: #fff2f0; color: #ff4d4f; border: 1px solid #ffccc7; }
        .ant-tag-default { background: #fafafa; color: #595959; border: 1px solid #d9d9d9; }
        .ant-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .ant-alert-success { background: #f6ffed; border: 1px solid #b7eb8f; color: #52c41a; }
        .ant-alert-warning { background: #fffbe6; border: 1px solid #ffe58f; color: #faad14; }
        .ant-alert-error { background: #fff2f0; border: 1px solid #ffccc7; color: #ff4d4f; }
        .ant-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ant-table thead th { background: #fafafa; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 1px solid #f0f0f0; }
        .ant-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; }
        .ant-table tbody tr:hover { background: #fafafa; }
        .ant-checkbox { width: 16px; height: 16px; cursor: pointer; }
        .text-center { text-align: center; }
        .text-muted { color: #8c8c8c; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }
        .mt-3 { margin-top: 16px; }
        .mt-4 { margin-top: 24px; }
        .d-flex { display: flex; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 16px; }
        .flex-column { flex-direction: column; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .w-100 { width: 100%; }
        .d-none { display: none; }
        .spinner { border: 2px solid #f3f3f3; border-top: 2px solid #1890ff; border-radius: 50%; width: 16px; height: 16px; animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .header-brand { color: white; font-size: 20px; font-weight: 600; }
        .header-actions { display: flex; align-items: center; gap: 16px; }
        .header-user { color: white; font-size: 14px; }
        .ant-btn-outline { background: transparent; color: white; border-color: white; }
        .ant-btn-outline:hover { background: rgba(255,255,255,0.1); }
        footer { text-align: center; color: #8c8c8c; padding: 24px; margin-top: 32px; font-size: 13px; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="ant-layout">
        <header class="ant-layout-header">
            <span class="header-brand">🏭 Compliance Engine</span>
            <div class="header-actions">
                @if(isset($subscription))
                    <span class="ant-tag {{ $subscription === 'FULL' ? 'ant-tag-success' : 'ant-tag-default' }}">
                        {{ $subscription }}
                    </span>
                @endif
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
            <small>Compliance Engine | Laravel 12 | Production Ready</small>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
