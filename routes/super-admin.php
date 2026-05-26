<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\BatchesController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AuditDetailsController;
use App\Http\Controllers\SuperAdmin\AuditFailuresController;
use App\Http\Controllers\SuperAdmin\PendingFilingsController;
use App\Http\Controllers\SuperAdmin\FormUpdatesController;
use App\Http\Controllers\SuperAdmin\NotificationController;
use App\Http\Controllers\SuperAdmin\PasswordController;

Route::prefix('super-admin')
    ->middleware(['web', 'auth', 'super.admin'])
    ->name('super-admin.')
    ->group(function () {

        // ── Existing: Tenant & User Management Dashboard ──────────────────────
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

        // Tenants
        Route::get('/tenants',                    [SuperAdminController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/create',             [SuperAdminController::class, 'createTenant'])->name('tenants.create');
        Route::post('/tenants',                   [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
        Route::get('/tenants/{tenant}/edit',      [SuperAdminController::class, 'editTenant'])->name('tenants.edit');
        Route::put('/tenants/{tenant}',           [SuperAdminController::class, 'updateTenant'])->name('tenants.update');
        Route::delete('/tenants/{tenant}',        [SuperAdminController::class, 'deleteTenant'])->name('tenants.delete');
        Route::post('/tenants/{tenant}/toggle',   [SuperAdminController::class, 'toggleSubscription'])->name('tenants.toggle');

        // Users
        Route::get('/users',                 [SuperAdminController::class, 'users'])->name('users');
        Route::get('/users/create',          [SuperAdminController::class, 'createUser'])->name('users.create');
        Route::post('/users',                [SuperAdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit',     [SuperAdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}',          [SuperAdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}',       [SuperAdminController::class, 'deleteUser'])->name('users.delete');

        // Batches
        Route::get('/batches',               [BatchesController::class, 'index'])->name('batches.index');
        Route::get('/batches/{id}',          [BatchesController::class, 'show'])->name('batches.show');

        // ── New: Analytics & Monitoring Panel ────────────────────────────────
        Route::get('/analytics',             [DashboardController::class, 'index'])->name('analytics');

        // Audit Details
        Route::get('/audit-details',         [AuditDetailsController::class, 'index'])->name('audit-details');
        Route::get('/audit-details/{id}',    [AuditDetailsController::class, 'show'])->name('audit-details.show');

        // Audit Failures
        Route::get('/audit-failures',        [AuditFailuresController::class, 'index'])->name('audit-failures');

        // Pending Filings
        Route::get('/pending-filings',       [PendingFilingsController::class, 'index'])->name('pending-filings');

        // Government Form Updates
        Route::get('/form-updates',          [FormUpdatesController::class, 'index'])->name('form-updates');

        // Notification API
        Route::get('/notifications/forms',   [NotificationController::class, 'getFormUpdates'])->name('notifications.forms');
        Route::get('/notifications/errors',  [NotificationController::class, 'getSystemErrors'])->name('notifications.errors');

        // Change Password
        Route::get('/change-password',       [PasswordController::class, 'showChangeForm'])->name('change-password');
        Route::post('/change-password',      [PasswordController::class, 'updatePassword'])->name('change-password.update');


    });
