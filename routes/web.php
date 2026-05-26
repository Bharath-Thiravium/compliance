<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');

// ── Ops helpers (token-protected, no shell access needed) ─────────────────────

Route::get('/_ops/optimize-clear', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $output = [];
    Artisan::call('optimize:clear'); $output['optimize:clear'] = trim(Artisan::output());
    Artisan::call('config:cache');   $output['config:cache']   = trim(Artisan::output());
    Artisan::call('route:cache');    $output['route:cache']    = trim(Artisan::output());
    Artisan::call('view:cache');     $output['view:cache']     = trim(Artisan::output());

    return response()->json(['ok' => true, 'output' => $output]);
});

Route::get('/_ops/migrate', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $output = [];
    Artisan::call('migrate', ['--force' => true]);
    $output['migrate'] = trim(Artisan::output());

    foreach ([
        storage_path('app/compliance_pdfs'),
        storage_path('app/compliance_inspection_packs'),
        storage_path('app/public'),
        storage_path('logs'),
        storage_path('framework/views'),
        storage_path('framework/cache'),
        storage_path('framework/sessions'),
    ] as $dir) {
        if (!is_dir($dir)) { mkdir($dir, 0755, true); $output['mkdir'][] = $dir; }
    }

    return response()->json(['ok' => true, 'output' => $output]);
});

// Recreates tables that are missing on production even though migrations log says they ran
Route::get('/_ops/fix-missing-tables', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $output = [];

    if (!Schema::hasTable('workforce_employee')) {
        Schema::create('workforce_employee', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->string('employee_code')->unique();
            $t->string('name');
            $t->string('pf_number')->nullable();
            $t->string('esi_number')->nullable();
            $t->date('date_of_joining');
            $t->string('designation')->nullable();
            $t->string('department')->nullable();
            $t->decimal('basic_salary', 12, 2)->default(0);
            $t->string('status')->default('active');
            $t->timestamps();
            $t->softDeletes();
        });
        $output[] = 'Created: workforce_employee';
    } else {
        $output[] = 'OK: workforce_employee already exists';
    }

    if (!Schema::hasTable('payroll_entries')) {
        Schema::create('payroll_entries', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->unsignedBigInteger('employee_id')->index();
            $t->unsignedBigInteger('payroll_cycle_id')->index();
            $t->integer('total_days_worked')->default(0);
            $t->integer('paid_leave_days')->default(0);
            $t->integer('unpaid_leave_days')->default(0);
            $t->decimal('overtime_hours', 8, 2)->default(0);
            $t->decimal('basic_earned', 12, 2)->default(0);
            $t->decimal('da_earned', 12, 2)->default(0);
            $t->decimal('hra_earned', 12, 2)->default(0);
            $t->decimal('other_allowances', 12, 2)->default(0);
            $t->decimal('overtime_wages', 12, 2)->default(0);
            $t->decimal('gross_salary', 12, 2)->default(0);
            $t->decimal('pf_employee', 12, 2)->default(0);
            $t->decimal('esi_employee', 12, 2)->default(0);
            $t->decimal('professional_tax', 12, 2)->default(0);
            $t->decimal('fines', 12, 2)->default(0);
            $t->decimal('advances', 12, 2)->default(0);
            $t->decimal('other_deductions', 12, 2)->default(0);
            $t->decimal('total_deductions', 12, 2)->default(0);
            $t->decimal('net_salary', 12, 2)->default(0);
            $t->date('payment_date')->nullable();
            $t->string('payment_mode')->nullable();
            $t->string('transaction_reference')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $output[] = 'Created: payroll_entries';
    } else {
        $output[] = 'OK: payroll_entries already exists';
    }

    foreach ([
        storage_path('app/compliance_pdfs'),
        storage_path('app/compliance_inspection_packs'),
    ] as $dir) {
        if (!is_dir($dir)) { mkdir($dir, 0755, true); $output[] = "Created dir: $dir"; }
        else $output[] = "OK dir: $dir";
    }

    return response()->json(['ok' => true, 'output' => $output]);
});

Route::get('/_ops/verify', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    // Quick sanity checks without running full diagnostics
    return response()->json([
        'ok'                    => true,
        'timestamp'             => now()->toDateTimeString(),
        'tables' => [
            'workforce_employee' => \Illuminate\Support\Facades\Schema::hasTable('workforce_employee'),
            'payroll_entries'    => \Illuminate\Support\Facades\Schema::hasTable('payroll_entries'),
        ],
        'storage_dirs' => [
            'compliance_pdfs'             => is_dir(storage_path('app/compliance_pdfs')),
            'compliance_inspection_packs' => is_dir(storage_path('app/compliance_inspection_packs')),
        ],
        'orchestrator_fix_live' => str_contains(
            file_get_contents(app_path('Services/Compliance/ComplianceOrchestrator.php')),
            'batch_id=0 means preview'
        ),
    ]);
});

Route::get('/_ops/logs', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) return response()->json(['ok' => false, 'error' => 'Log file not found']);

    $lines = max(10, min((int) $request->query('lines', 100), 500));
    $file  = new \SplFileObject($logFile, 'r');
    $file->seek(PHP_INT_MAX);
    $file->seek(max(0, $file->key() - $lines));

    $output = [];
    while (!$file->eof()) { $output[] = rtrim($file->current()); $file->next(); }

    return response(implode("\n", array_filter($output)), 200)
        ->header('Content-Type', 'text/plain; charset=utf-8');
});

Route::get('/_ops/session-check', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $session = $request->session();
    $key = '__ops_smoke';
    $session->put($key, ($session->get($key) ?? 0) + 1);
    $session->save();

    return response()->json([
        'ok'      => true,
        'request' => ['url' => $request->fullUrl(), 'is_secure' => $request->isSecure(), 'host' => $request->getHost()],
        'session' => ['driver' => config('session.driver'), 'id' => $session->getId(), 'smoke_counter' => $session->get($key)],
        'app'     => ['env' => config('app.env'), 'debug' => (bool) config('app.debug'), 'url' => config('app.url'), 'key_set' => !empty(config('app.key'))],
    ]);
});

// ── Application routes ────────────────────────────────────────────────────────

require __DIR__.'/compliance.php';
require __DIR__.'/batch-processing.php';
require __DIR__.'/data-input.php';
require __DIR__.'/super-admin.php';

Route::get('/', function () {
    return redirect()->route('compliance.dashboard');
});
