<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// One-time ops endpoint for hosts without shell access.
// Set OPS_TOKEN in the environment, hit /_ops/optimize-clear?token=... , then remove this route.
Route::get('/_ops/optimize-clear', function (Request $request) {
    $token = (string) config('app.ops_token', '');

    if ($token === '' || ! hash_equals($token, (string) $request->query('token', ''))) {
        abort(403);
    }

    $output = [];

    Artisan::call('optimize:clear');
    $output['optimize:clear'] = trim(Artisan::output());

    Artisan::call('config:cache');
    $output['config:cache'] = trim(Artisan::output());

    Artisan::call('route:cache');
    $output['route:cache'] = trim(Artisan::output());

    Artisan::call('view:cache');
    $output['view:cache'] = trim(Artisan::output());

    return response()->json([
        'ok'     => true,
        'output' => $output,
    ]);
});

// Mark already-run migrations as done so migrate --force stops re-attempting them.
// Use when the table exists on the server but is missing from the migrations log.
Route::get('/_ops/migrate', function (Request $request) {
    $token = (string) config('app.ops_token', '');

    if ($token === '' || ! hash_equals($token, (string) $request->query('token', ''))) {
        abort(403);
    }

    $output = [];

    Artisan::call('migrate', ['--force' => true]);
    $output['migrate'] = trim(Artisan::output());

    return response()->json([
        'ok'     => true,
        'output' => $output,
    ]);
});

// Show last N lines of the Laravel log — essential when there's no terminal access.
Route::get('/_ops/logs', function (Request $request) {
    $token = (string) config('app.ops_token', '');

    if ($token === '' || ! hash_equals($token, (string) $request->query('token', ''))) {
        abort(403);
    }

    $logFile = storage_path('logs/laravel.log');

    if (! file_exists($logFile)) {
        return response()->json(['ok' => false, 'error' => 'Log file not found']);
    }

    $lines = (int) ($request->query('lines', 100));
    $lines = max(10, min($lines, 500));

    // Read last N lines without loading the whole file into memory
    $file   = new \SplFileObject($logFile, 'r');
    $file->seek(PHP_INT_MAX);
    $total  = $file->key();
    $start  = max(0, $total - $lines);
    $file->seek($start);

    $output = [];
    while (! $file->eof()) {
        $output[] = rtrim($file->current());
        $file->next();
    }

    return response(implode("\n", array_filter($output)), 200)
        ->header('Content-Type', 'text/plain; charset=utf-8');
});

// Diagnostics for 419 / CSRF issues (remove after debugging).
Route::get('/_ops/session-check', function (Request $request) {
    $token = (string) config('app.ops_token', '');

    if ($token === '' || ! hash_equals($token, (string) $request->query('token', ''))) {
        abort(403);
    }

    $session = $request->session();
    $key = '__ops_smoke';
    $previous = $session->get($key);
    $session->put($key, ($previous ?? 0) + 1);
    $session->save();

    return response()->json([
        'ok' => true,
        'request' => [
            'url' => $request->fullUrl(),
            'is_secure' => $request->isSecure(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'path' => $request->getPathInfo(),
        ],
        'session' => [
            'driver' => config('session.driver'),
            'name' => config('session.cookie'),
            'id' => $session->getId(),
            'lifetime' => config('session.lifetime'),
            'path' => config('session.path'),
            'domain' => config('session.domain'),
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site'),
            'smoke_counter' => $session->get($key),
        ],
        'app' => [
            'env' => config('app.env'),
            'debug' => (bool) config('app.debug'),
            'url' => config('app.url'),
            'key_set' => ! empty(config('app.key')),
        ],
    ]);
});

require __DIR__.'/compliance.php';
require __DIR__.'/batch-processing.php';
require __DIR__.'/data-input.php';
require __DIR__.'/super-admin.php';

Route::get('/', function () {
    return redirect()->route('compliance.dashboard');
});
