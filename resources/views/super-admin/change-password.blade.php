@extends('super-admin.layouts.app')

@section('title', 'Change Password - Super Admin')

@section('content')
    <div class="mb-4">
        <a href="{{ route('super-admin.dashboard') }}" class="ant-btn">← Back to Dashboard</a>
    </div>

    <div class="ant-card" style="max-width: 600px; margin: 0 auto;">
        <div class="ant-card-head">🔒 Change Password</div>
        <div class="ant-card-body">
            @if(session('success'))
                <div style="padding: 12px 16px; background: #f6ffed; border: 1px solid #b7eb8f; border-radius: 6px; color: #52c41a; margin-bottom: 16px;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="padding: 12px 16px; background: #fff2f0; border: 1px solid #ffccc7; border-radius: 6px; color: #ff4d4f; margin-bottom: 16px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.change-password.update') }}">
                @csrf

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #262626;">Current Password</label>
                    <input 
                        type="password" 
                        name="current_password" 
                        required
                        style="width: 100%; height: 40px; padding: 4px 11px; border: 1px solid #d9d9d9; border-radius: 6px; font-size: 14px;"
                    >
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #262626;">New Password</label>
                    <input 
                        type="password" 
                        name="new_password" 
                        required
                        minlength="8"
                        style="width: 100%; height: 40px; padding: 4px 11px; border: 1px solid #d9d9d9; border-radius: 6px; font-size: 14px;"
                    >
                    <small style="color: #8c8c8c; font-size: 12px;">Minimum 8 characters</small>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #262626;">Confirm New Password</label>
                    <input 
                        type="password" 
                        name="new_password_confirmation" 
                        required
                        minlength="8"
                        style="width: 100%; height: 40px; padding: 4px 11px; border: 1px solid #d9d9d9; border-radius: 6px; font-size: 14px;"
                    >
                </div>

                <button 
                    type="submit" 
                    class="ant-btn ant-btn-primary" 
                    style="width: 100%; height: 48px; font-size: 16px;"
                >
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <div class="ant-card mt-4" style="max-width: 600px; margin: 24px auto 0;">
        <div class="ant-card-head info">ℹ️ Password Requirements</div>
        <div class="ant-card-body">
            <ul style="margin: 0; padding-left: 20px; color: #595959;">
                <li>Minimum 8 characters</li>
                <li>Must match confirmation password</li>
                <li>Current password must be correct</li>
            </ul>
        </div>
    </div>
@endsection
