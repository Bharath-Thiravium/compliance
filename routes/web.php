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
    $token = (string) env('OPS_TOKEN', '');

    if ($token === '' || ! hash_equals($token, (string) $request->query('token', ''))) {
        abort(403);
    }

    Artisan::call('optimize:clear');

    return response()->json([
        'ok' => true,
        'output' => Artisan::output(),
    ]);
});

require __DIR__.'/compliance.php';
require __DIR__.'/batch-processing.php';
require __DIR__.'/data-input.php';
require __DIR__.'/super-admin.php';

Route::get('/', function () {
    return redirect()->route('compliance.dashboard');
});
