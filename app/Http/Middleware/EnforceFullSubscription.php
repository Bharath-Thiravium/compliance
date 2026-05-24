<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceFullSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            // Super admins have no tenant — let them pass through freely
            if ($user && $user->is_super_admin) {
                return $next($request);
            }
            abort(403, 'No tenant associated with user');
        }

        $tenant = \DB::table('tenants')->where('id', $user->tenant_id)->first();
        
        if (!$tenant || $tenant->subscription_type !== 'FULL') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature requires FULL subscription. Please upgrade your plan.',
                ], 403);
            }

            return redirect()->route('compliance.dashboard')
                ->with('error', 'This feature requires FULL subscription. Please upgrade your plan.');
        }

        return $next($request);
    }
}