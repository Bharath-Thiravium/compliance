@extends('super-admin.layout')

@section('title', 'Change Password - Super Admin')
@section('page-title', 'Change Password')

@section('content')
    <div class="mb-4">
        <a href="{{ route('super-admin.dashboard') }}" class="btn">← Back to Dashboard</a>
    </div>

    <div class="card" style="max-width:600px; margin:0 auto;">
        <div class="card-header">🔒 Change Password</div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success mb-3">✅ {{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.change-password.update') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" required class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" required minlength="8" class="form-input">
                    <small class="text-muted text-xs">Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required minlength="8" class="form-input">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <div class="card section-spacing" style="max-width:600px; margin:24px auto 0;">
        <div class="card-header info">ℹ️ Password Requirements</div>
        <div class="card-body">
            <ul style="margin:0;padding-left:20px;color:#595959;">
                <li>Minimum 8 characters</li>
                <li>Must match confirmation password</li>
                <li>Current password must be correct</li>
            </ul>
        </div>
    </div>
@endsection
