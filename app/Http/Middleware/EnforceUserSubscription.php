<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceUserSubscription
{
    public function handle(Request $request, Closure $next, string $requiredType = 'FULL')
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->tenant->subscription_type !== $requiredType) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "This feature requires {$requiredType} subscription."
                ], 403);
            }

            return redirect()->route('compliance.dashboard')
                ->with('error', "This feature requires {$requiredType} subscription.");
        }

        return $next($request);
    }
}
