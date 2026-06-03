<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/diagnostics.php';

Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// ── CSRF token route (for legacy blade templates) ──────────────────────────────
Route::get('/_csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('app.csrf.token');

// ── Ops helpers (token-protected) ─────────────────────────────────────────────

Route::middleware([])->group(function () {
    Route::get('/_ops/optimize-clear', function (Request $request) {
        $token = (string) (env('OPS_TOKEN') ?: config('app.ops_token', ''));
        
        $providedToken = (string) $request->query('token', '');
        $isTokenValid = ($token !== '' && hash_equals($token, $providedToken));
        
        \Illuminate\Support\Facades\Log::info('Ops Optimize Clear: Token validation attempt.', [
            'token_configured' => ($token !== ''),
            'token_matched' => $isTokenValid,
            'ip' => $request->ip(),
        ]);

        if (!$isTokenValid) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token'
            ], 403);
        }

        try {
            \Illuminate\Support\Facades\Log::info('Ops Optimize Clear: Starting optimize:clear...');

            Artisan::call('optimize:clear');
            $artisanOutput = trim(Artisan::output());

            \Illuminate\Support\Facades\Log::info('Ops Optimize Clear: Completed optimize:clear successfully.', [
                'output' => $artisanOutput,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application cache cleared successfully',
                'output' => $artisanOutput,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Ops Optimize Clear: Exception occurred during execution.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during cache clearing: ' . $e->getMessage(),
            ], 500);
        }
    })->withoutMiddleware('web');
});

Route::get('/_ops/migrate', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);
    $out = [];
    Artisan::call('migrate', ['--force' => true]);
    $out['migrate'] = trim(Artisan::output());
    foreach ([
        storage_path('app/compliance_pdfs'),
        storage_path('app/compliance_inspection_packs'),
        storage_path('app/public'),
        storage_path('logs'),
        storage_path('framework/views'),
        storage_path('framework/cache'),
        storage_path('framework/sessions'),
    ] as $dir) {
        if (!is_dir($dir)) { mkdir($dir, 0755, true); $out['mkdir'][] = $dir; }
    }
    return response()->json(['ok' => true, 'output' => $out]);
});

require __DIR__.'/compliance.php';
require __DIR__.'/batch-processing.php';
require __DIR__.'/data-input.php';
require __DIR__.'/super-admin.php';
require __DIR__.'/smart-templates.php';

Route::get('/', function () {
    return redirect()->route('login');
});
