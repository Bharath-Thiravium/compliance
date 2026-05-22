<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_tenants'   => Tenant::count(),
            'total_users'     => User::where('is_super_admin', false)->count(),
            'full_tenants'    => Tenant::where('subscription_type', 'FULL')->count(),
            'minimal_tenants' => Tenant::where('subscription_type', 'MINIMAL')->count(),
        ];

        $recent_tenants = Tenant::withCount('users')->latest()->take(5)->get();

        return view('super-admin.dashboard', compact('stats', 'recent_tenants'));
    }

    // ── Tenants ───────────────────────────────────────────────────────────────

    public function tenants(Request $request)
    {
        $query = Tenant::withCount('users')->with('users');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('subscription')) {
            $query->where('subscription_type', $request->subscription);
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function createTenant()
    {
        return view('super-admin.tenants.create');
    }

    public function storeTenant(Request $request)
    {
        $request->validate([
            'company_name'      => 'required|string|max:255',
            'subscription_type' => 'required|in:FULL,MINIMAL',
            'branch_name'       => 'required|string|max:255',
            'admin_name'        => 'required|string|max:255',
            'admin_email'       => 'required|email|unique:users,email',
            'admin_password'    => 'required|min:8',
        ]);

        DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name'              => $request->company_name,
                'subscription_type' => $request->subscription_type,
            ]);

            // Required by BatchOrchestrator — must exist before any batch can be created
            \App\Models\Branch::withoutGlobalScopes()->create([
                'tenant_id'              => $tenant->id,
                'branch_name'            => $request->branch_name,
                'factory_license_number' => $request->factory_license_number ?: null,
                'address'                => $request->address ?: null,
            ]);

            User::create([
                'tenant_id'      => $tenant->id,
                'name'           => $request->admin_name,
                'email'          => $request->admin_email,
                'password'       => Hash::make($request->admin_password),
                'is_super_admin' => false,
            ]);
        });

        return redirect()->route('super-admin.tenants')
            ->with('success', 'Tenant "' . $request->company_name . '" created with branch and admin user successfully.');
    }

    public function editTenant(Tenant $tenant)
    {
        $branch = \App\Models\Branch::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
        return view('super-admin.tenants.edit', compact('tenant', 'branch'));
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'subscription_type' => 'required|in:FULL,MINIMAL',
            'branch_name'       => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $tenant) {
            $tenant->update($request->only('name', 'subscription_type'));

            // Upsert branch — create if missing, update if exists
            \App\Models\Branch::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'branch_name'            => $request->branch_name,
                    'factory_license_number' => $request->factory_license_number ?: null,
                    'address'                => $request->address ?: null,
                ]
            );
        });

        return redirect()->route('super-admin.tenants')->with('success', 'Tenant updated.');
    }

    public function deleteTenant(Tenant $tenant)
    {
        if ($tenant->users()->count() > 0) {
            return back()->with('error', 'Cannot delete tenant with existing users. Remove users first.');
        }

        $tenant->delete();

        return redirect()->route('super-admin.tenants')->with('success', 'Tenant deleted.');
    }

    // ── Users ─────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::with('tenant')->where('is_super_admin', false);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $users   = $query->latest()->paginate(15)->withQueryString();
        $tenants = Tenant::orderBy('name')->get();

        return view('super-admin.users.index', compact('users', 'tenants'));
    }

    public function createUser()
    {
        $tenants = Tenant::orderBy('name')->get();
        return view('super-admin.users.create', compact('tenants'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);

        User::create([
            'tenant_id'      => $tenant->id,
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'is_super_admin' => false,
        ]);

        return redirect()->route('super-admin.users')->with('success', 'User created and linked to tenant "' . $tenant->name . '" successfully.');
    }

    public function editUser(User $user)
    {
        $tenants = Tenant::orderBy('name')->get();
        return view('super-admin.users.edit', compact('user', 'tenants'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
        ]);

        $data = $request->only('tenant_id', 'name', 'email');
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('super-admin.users')->with('success', 'User updated.');
    }

    public function deleteUser(User $user)
    {
        if ($user->is_super_admin) {
            return back()->with('error', 'Cannot delete a Super Admin account.');
        }

        $user->delete();

        return redirect()->route('super-admin.users')->with('success', 'User deleted.');
    }

    public function toggleSubscription(Tenant $tenant)
    {
        $tenant->update([
            'subscription_type' => $tenant->subscription_type === 'FULL' ? 'MINIMAL' : 'FULL',
        ]);

        return back()->with('success', "Subscription changed to {$tenant->subscription_type}.");
    }
}
