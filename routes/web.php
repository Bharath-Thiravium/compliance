<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

require __DIR__.'/compliance.php';
require __DIR__.'/batch-processing.php';
require __DIR__.'/data-input.php';
require __DIR__.'/super-admin.php';

Route::get('/', function () {
    return redirect()->route('compliance.dashboard');
});
