<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// ── Ops helpers (token-protected) ─────────────────────────────────────────────

Route::get('/_ops/optimize-clear', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);
    $out = [];
    Artisan::call('optimize:clear'); $out['optimize:clear'] = trim(Artisan::output());
    Artisan::call('config:cache');   $out['config:cache']   = trim(Artisan::output());
    Artisan::call('route:cache');    $out['route:cache']    = trim(Artisan::output());
    Artisan::call('view:cache');     $out['view:cache']     = trim(Artisan::output());
    return response()->json(['ok' => true, 'output' => $out]);
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

Route::get('/_ops/fix-missing-tables', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);
    $out = [];

    if (!Schema::hasTable('payroll_cycles')) {
        Schema::create('payroll_cycles', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->string('cycle_name');
            $t->date('period_from');
            $t->date('period_to');
            $t->timestamp('processed_at')->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        $out[] = 'Created: payroll_cycles';
    } else {
        $out[] = 'OK: payroll_cycles exists';
    }

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
        $out[] = 'Created: workforce_employee';
    } else {
        $out[] = 'OK: workforce_employee exists';
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
        $out[] = 'Created: payroll_entries';
    } else {
        $out[] = 'OK: payroll_entries exists';
    }

    foreach ([
        storage_path('app/compliance_pdfs'),
        storage_path('app/compliance_inspection_packs'),
    ] as $dir) {
        if (!is_dir($dir)) { mkdir($dir, 0755, true); $out[] = "Created dir: $dir"; }
        else $out[] = "OK dir: $dir";
    }

    return response()->json(['ok' => true, 'output' => $out]);
});

Route::get('/_ops/verify', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);
    $orchPath = app_path('Services/Compliance/ComplianceOrchestrator.php');
    return response()->json([
        'ok'        => true,
        'timestamp' => now()->toDateTimeString(),
        'tables'    => [
            'workforce_employee' => Schema::hasTable('workforce_employee'),
            'payroll_entries'    => Schema::hasTable('payroll_entries'),
        ],
        'storage_dirs' => [
            'compliance_pdfs'             => is_dir(storage_path('app/compliance_pdfs')),
            'compliance_inspection_packs' => is_dir(storage_path('app/compliance_inspection_packs')),
        ],
        'orchestrator_fix_live' => file_exists($orchPath) && str_contains(
            file_get_contents($orchPath), 'batch_id=0 means preview'
        ),
    ]);
});

Route::get('/_ops/diff', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $env = [
        'APP_ENV'        => config('app.env'),
        'APP_DEBUG'      => config('app.debug') ? 'true' : 'false',
        'APP_URL'        => config('app.url'),
        'APP_KEY_SET'    => config('app.key') ? 'yes' : 'NO',
        'DB_HOST'        => config('database.connections.mysql.host'),
        'DB_DATABASE'    => config('database.connections.mysql.database'),
        'PHP_VERSION'    => PHP_VERSION,
        'LARAVEL'        => app()->version(),
        'CACHE_DRIVER'   => config('cache.default'),
        'SESSION_DRIVER' => config('session.driver'),
        'QUEUE_CONN'     => config('queue.default'),
    ];

    $tableNames = [
        'users','tenants','branches','compliance_execution_batches',
        'compliance_batch_forms','compliance_forms_master','compliance_sections',
        'compliance_generation_logs','compliance_audit_logs',
        'compliance_form_audit_scores','compliance_manual_master',
        'compliance_manual_batch_items','workforce_employee',
        'workforce_attendance','payroll_entries','bonus_records',
        'payroll_cycles','migrations',
    ];
    $dbTables = [];
    foreach ($tableNames as $t) {
        try {
            $dbTables[$t] = Schema::hasTable($t) ? DB::table($t)->count() : 'MISSING';
        } catch (\Throwable $e) {
            $dbTables[$t] = 'ERROR: ' . $e->getMessage();
        }
    }

    $migFiles = count(glob(database_path('migrations/*.php')) ?: []);
    $migRan   = 0;
    try { $migRan = DB::table('migrations')->count(); } catch (\Throwable) {}

    $apiList   = array_values(array_map('basename', array_filter(
        glob(app_path('Services/Compliance/FormApis/*ApiService.php')) ?: [],
        fn($f) => !in_array(basename($f), ['BaseFormApiService.php', 'FormApiServiceFactory.php'])
    )));
    $genList   = array_values(array_map('basename', array_filter(
        glob(app_path('Services/Compliance/FormGenerator/*Generator.php')) ?: [],
        fn($f) => basename($f) !== 'BaseFormGenerator.php'
    )));
    $bladeList = array_values(array_map('basename',
        glob(resource_path('views/compliance/forms/*.blade.php')) ?: []
    ));
    sort($apiList); sort($genList); sort($bladeList);

    $keyFiles = [
        'ComplianceOrchestrator'    => app_path('Services/Compliance/ComplianceOrchestrator.php'),
        'BatchProcessingController' => app_path('Http/Controllers/BatchProcessingController.php'),
        'ComplianceTestAnalyzer'    => app_path('Services/Compliance/Testing/ComplianceTestAnalyzer.php'),
        'web.php'                   => base_path('routes/web.php'),
        'AppServiceProvider'        => app_path('Providers/AppServiceProvider.php'),
        'dashboard.blade.php'       => resource_path('views/compliance/dashboard.blade.php'),
    ];
    $fileSizes = [];
    foreach ($keyFiles as $name => $path) {
        $fileSizes[$name] = file_exists($path) ? filesize($path) . ' bytes' : 'MISSING';
    }

    $orchPath = app_path('Services/Compliance/ComplianceOrchestrator.php');

    return response()->json([
        'server'           => 'production',
        'timestamp'        => now()->toDateTimeString(),
        'env'              => $env,
        'db_tables'        => $dbTables,
        'migrations'       => ['files' => $migFiles, 'ran' => $migRan, 'pending' => $migFiles - $migRan],
        'file_counts'      => ['api_services' => count($apiList), 'generators' => count($genList), 'blade_templates' => count($bladeList)],
        'api_services'     => $apiList,
        'generators'       => $genList,
        'blade_templates'  => $bladeList,
        'key_file_sizes'   => $fileSizes,
        'orchestrator_fix' => file_exists($orchPath) && str_contains(file_get_contents($orchPath), 'batch_id=0 means preview'),
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
    $out = [];
    while (!$file->eof()) { $out[] = rtrim($file->current()); $file->next(); }
    return response(implode("\n", array_filter($out)), 200)
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
