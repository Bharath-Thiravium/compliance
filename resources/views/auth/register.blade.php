<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Compliance Engine</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.13.0/reset.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .login-container { max-width: 440px; margin: 0 auto; padding: 20px; width: 100%; }
        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 40px;
        }
        .login-header { text-align: center; margin-bottom: 28px; }
        .login-title { font-size: 24px; font-weight: 600; margin-bottom: 8px; color: #262626; }
        .login-subtitle { color: #8c8c8c; font-size: 14px; }
        .form-item { margin-bottom: 20px; }
        .form-label { font-weight: 500; color: #262626; margin-bottom: 6px; display: block; font-size: 14px; }
        .ant-input, .ant-select {
            height: 40px;
            padding: 4px 11px;
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .ant-input:hover, .ant-select:hover { border-color: #4096ff; }
        .ant-input:focus, .ant-select:focus {
            border-color: #4096ff;
            box-shadow: 0 0 0 2px rgba(24,144,255,0.1);
            outline: none;
        }
        .plan-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .plan-card {
            border: 2px solid #d9d9d9;
            border-radius: 8px;
            padding: 16px 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .plan-card:hover { border-color: #4096ff; }
        .plan-card input[type="radio"] { display: none; }
        .plan-card.selected { border-color: #1890ff; background: #e6f4ff; }
        .plan-name { font-weight: 600; font-size: 15px; color: #262626; margin-bottom: 4px; }
        .plan-desc { font-size: 12px; color: #8c8c8c; line-height: 1.4; }
        .plan-badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .badge-full { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .badge-minimal { background: #fff7e6; color: #fa8c16; border: 1px solid #ffd591; }
        .ant-btn {
            height: 40px;
            padding: 4px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        .ant-btn-primary { background: #1890ff; color: white; }
        .ant-btn-primary:hover { background: #40a9ff; }
        .ant-alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .ant-alert-error { background: #fff2f0; border: 1px solid #ffccc7; color: #cf1322; }
        .divider { border: none; border-top: 1px solid #f0f0f0; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2 class="login-title">🏭 Compliance Engine</h2>
                <p class="login-subtitle">Create your account</p>
            </div>

            @if($errors->any())
                <div class="ant-alert ant-alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="form-item">
                    <label class="form-label">Company Name</label>
                    <input type="text" class="ant-input" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Acme Industries Pvt Ltd" required autofocus>
                </div>

                <div class="form-item">
                    <label class="form-label">Your Name</label>
                    <input type="text" class="ant-input" name="name" value="{{ old('name') }}" placeholder="Full name" required>
                </div>

                <div class="form-item">
                    <label class="form-label">Email</label>
                    <input type="email" class="ant-input" name="email" value="{{ old('email') }}" placeholder="admin@company.com" required>
                </div>

                <div class="form-item">
                    <label class="form-label">Password</label>
                    <input type="password" class="ant-input" name="password" placeholder="Min. 8 characters" required>
                </div>

                <div class="form-item">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="ant-input" name="password_confirmation" placeholder="Re-enter password" required>
                </div>

                <hr class="divider">

                <div class="form-item">
                    <label class="form-label">Subscription Plan</label>
                    <div class="plan-grid" id="planGrid">
                        <label class="plan-card {{ old('subscription_type', 'FULL') === 'FULL' ? 'selected' : '' }}" onclick="selectPlan(this, 'FULL')">
                            <input type="radio" name="subscription_type" value="FULL" {{ old('subscription_type', 'FULL') === 'FULL' ? 'checked' : '' }}>
                            <div class="plan-badge badge-full">FULL</div>
                            <div class="plan-name">Full Access</div>
                            <div class="plan-desc">All 34 forms, auto-generation, bulk automation & digital signatures</div>
                        </label>
                        <label class="plan-card {{ old('subscription_type') === 'MINIMAL' ? 'selected' : '' }}" onclick="selectPlan(this, 'MINIMAL')">
                            <input type="radio" name="subscription_type" value="MINIMAL" {{ old('subscription_type') === 'MINIMAL' ? 'checked' : '' }}>
                            <div class="plan-badge badge-minimal">MINIMAL</div>
                            <div class="plan-name">Minimal</div>
                            <div class="plan-desc">Core forms, manual upload & analysis reports only</div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="ant-btn ant-btn-primary" style="margin-top:8px;">Create Account</button>
            </form>

            <div style="text-align:center; margin-top:20px; font-size:14px; color:#8c8c8c;">
                Already have an account?
                <a href="{{ route('login') }}" style="color:#1890ff; font-weight:500;">Sign In</a>
            </div>
        </div>
    </div>

    <script>
        function selectPlan(el, value) {
            document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input[type="radio"]').checked = true;
        }
    </script>
</body>
</html>
