<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ComplianceUserAccessController extends Controller
{
    public function index()
    {
        $users = User::with('tenant')
            ->where('is_super_admin', false)
            ->get()
            ->map(fn($user) => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role ?? 'user',
                'tenant' => optional($user->tenant)->name ?? 'N/A',
                'label'  => ucfirst(str_replace('_', ' ', $user->role ?? 'User')),
            ]);

        return view('super-admin.compliance-users', compact('users'));
    }

    public function open($id)
    {
        $user = User::findOrFail($id);

        session(['impersonated_user_id' => $user->id, 'impersonated_user_name' => $user->name]);

        return redirect()->route('compliance.dashboard');
    }

    public function stopImpersonation()
    {
        session()->forget(['impersonated_user_id', 'impersonated_user_name']);

        return redirect()->route('super-admin.dashboard')->with('success', 'Stopped viewing as user.');
    }
}
