<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('compliance.dashboard');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            try {
                AuditLog::create([
                    'tenant_id'  => $user->tenant_id ?? 0,
                    'user_id'    => $user->id,
                    'action'     => 'login',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata'   => ['status' => 'success', 'email' => $user->email],
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {}

            if ($user->is_super_admin) {
                return redirect()->route('super-admin.dashboard');
            }

            return redirect()->intended(route('compliance.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showRegister()
    {
        return redirect()->route('login');
    }

    public function register()
    {
        return redirect()->route('login');
    }
}
