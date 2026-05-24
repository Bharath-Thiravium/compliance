<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Compliance Engine</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/antd/5.13.0/reset.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #262626;
        }
        .login-subtitle {
            color: #8c8c8c;
            font-size: 14px;
        }
        .ant-form-item {
            margin-bottom: 24px;
        }
        .ant-form-item-label {
            font-weight: 500;
            color: #262626;
            margin-bottom: 8px;
            display: block;
        }
        .ant-input {
            height: 40px;
            padding: 4px 11px;
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s;
        }
        .ant-input:hover {
            border-color: #4096ff;
        }
        .ant-input:focus {
            border-color: #4096ff;
            box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.1);
            outline: none;
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
        }
        .ant-btn-primary {
            background: #1890ff;
            color: white;
        }
        .ant-btn-primary:hover {
            background: #40a9ff;
        }
        .ant-alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .ant-alert-error {
            background: #fff2f0;
            border: 1px solid #ffccc7;
            color: #cf1322;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2 class="login-title">🏭 Compliance Engine</h2>
                <p class="login-subtitle">Sign in to your account</p>
            </div>

            @if(session('error'))
                <div class="ant-alert ant-alert-error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="ant-alert ant-alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="ant-form-item">
                    <label for="email" class="ant-form-item-label">Email</label>
                    <input type="email" class="ant-input" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="ant-form-item">
                    <label for="password" class="ant-form-item-label">Password</label>
                    <input type="password" class="ant-input" id="password" name="password" required>
                </div>

                <button type="submit" class="ant-btn ant-btn-primary">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
