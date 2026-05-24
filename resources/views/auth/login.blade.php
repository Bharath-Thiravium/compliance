<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · Compliance Engine</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="color-scheme" content="light">
    <link rel="icon" href="{{ url('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.13.0/reset.min.css">
    <style>
        :root{
            --bg1:#667eea;
            --bg2:#764ba2;
            --card:#ffffff;
            --text:#262626;
            --muted:#8c8c8c;
            --border:#d9d9d9;
            --primary:#1677ff;
            --primaryHover:#4096ff;
            --dangerBg:#fff2f0;
            --dangerBorder:#ffccc7;
            --dangerText:#cf1322;
            --shadow: 0 10px 30px rgba(0,0,0,0.20);
        }
        body {
            background: linear-gradient(135deg, var(--bg1) 0%, var(--bg2) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text);
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
        }
        .login-card {
            background: var(--card);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 36px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
            letter-spacing: -0.2px;
        }
        .login-subtitle {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.4;
        }
        .ant-form-item {
            margin-bottom: 24px;
        }
        .ant-form-item-label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 6px;
            display: block;
        }
        .ant-input {
            height: 40px;
            padding: 4px 11px;
            border: 1px solid var(--border);
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s;
            background: #fff;
        }
        .ant-input:hover {
            border-color: var(--primaryHover);
        }
        .ant-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(22, 119, 255, 0.15);
            outline: none;
        }
        .ant-input[aria-invalid="true"]{
            border-color: var(--dangerText);
            box-shadow: 0 0 0 3px rgba(207, 19, 34, 0.10);
        }
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .ant-btn-primary {
            background: var(--primary);
            color: white;
        }
        .ant-btn-primary:hover {
            background: var(--primaryHover);
        }
        .ant-btn:disabled{
            opacity: 0.6;
            cursor: not-allowed;
        }
        .ant-alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .ant-alert-error {
            background: var(--dangerBg);
            border: 1px solid var(--dangerBorder);
            color: var(--dangerText);
        }
        .row{
            display:flex;
            justify-content: space-between;
            align-items:center;
            gap: 12px;
            margin-top: -6px;
            margin-bottom: 18px;
        }
        .checkbox{
            display:flex;
            align-items:center;
            gap: 8px;
            font-size: 14px;
            color: var(--text);
            user-select:none;
        }
        .checkbox input{
            width: 16px;
            height: 16px;
        }
        .help{
            font-size: 12px;
            color: var(--muted);
            margin-top: 6px;
        }
        .sr-only{
            position:absolute;
            width:1px;
            height:1px;
            padding:0;
            margin:-1px;
            overflow:hidden;
            clip:rect(0,0,0,0);
            border:0;
        }
        @media (max-width: 420px){
            .login-card{ padding: 28px; border-radius: 12px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Compliance Engine</h1>
                <p class="login-subtitle">Sign in with your work email and password.</p>
            </div>

            @if(session('error'))
                <div class="ant-alert ant-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="ant-alert ant-alert-error" role="alert" aria-live="polite">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf
                <div class="ant-form-item">
                    <label for="email" class="ant-form-item-label">Email</label>
                    <input
                        type="email"
                        class="ant-input"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        inputmode="email"
                        required
                        autofocus
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                    <div class="help">Example: name@company.com</div>
                </div>

                <div class="ant-form-item">
                    <label for="password" class="ant-form-item-label">Password</label>
                    <input
                        type="password"
                        class="ant-input"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    >
                </div>

                <div class="row">
                    <label class="checkbox" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    <span class="sr-only" aria-hidden="true"></span>
                </div>

                <button type="submit" class="ant-btn ant-btn-primary">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
