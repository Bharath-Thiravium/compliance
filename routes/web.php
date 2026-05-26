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

Route::get('/_ops/deploy', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);
    $out = [];
    Artisan::call('optimize:clear'); $out['optimize:clear'] = trim(Artisan::output());
    Artisan::call('config:cache');   $out['config:cache']   = trim(Artisan::output());
    Artisan::call('route:cache');    $out['route:cache']    = trim(Artisan::output());
    Artisan::call('view:cache');     $out['view:cache']     = trim(Artisan::output());
    return response()->json(['ok' => true, 'output' => $out]);
});

Route::get('/_ops/form-test', function (Request $request) {
    $token = (string) config('app.ops_token', '');
    if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) abort(403);

    $tenantId = (int) $request->query('tenant_id', 2);
    $branchId = (int) $request->query('branch_id', 2);
    $month    = (int) $request->query('month', 12);
    $year     = (int) $request->query('year', 2025);

    $formCodes = [
        'CLRA'             => ['FORM_XII','FORM_XIII','FORM_XIV','FORM_XVI','FORM_XVII','FORM_XIX','FORM_XX','FORM_XXI','FORM_XXII','FORM_XXIII'],
        'Labour Welfare'   => ['FORM_A','FORM_C','FORM_D','FORM_D_ER'],
        'Social Security'  => ['FORM_11','ESI_FORM_12'],
        'Factories Act'    => ['FORM_B','FORM_2','FORM_8','FORM_10','FORM_12','FORM_17','FORM_18','FORM_25','FORM_26','FORM_26A','HAZARD_REG'],
        'Shops'            => ['SHOPS_FORM_12','SHOPS_FORM_13','SHOPS_FORM_C','SHOPS_FORM_VI','SHOPS_UNPAID','SHOPS_FINES'],
    ];

    $results = [];
    $pass = $fail = $empty = 0;

    foreach ($formCodes as $group => $codes) {
        foreach ($codes as $code) {
            $start = microtime(true);
            try {
                $svc = \App\Services\Compliance\FormApis\FormApiServiceFactory::make($code);
                if (!$svc) throw new \Exception('No API service registered');

                $raw = $svc->fetch($tenantId, $branchId, $month, $year);
                $records = count($raw['records'] ?? []);

                $gen = \App\Services\Compliance\FormGenerator\FormGeneratorFactory::make($code);
                if (!$gen) throw new \Exception('No generator registered');

                $data = $gen->generate($raw);
                $rows = count($data['rows'] ?? $data['entries'] ?? []);
                $ms   = round((microtime(true) - $start) * 1000);

                if ($rows === 0) {
                    $empty++;
                    $results[$group][] = ['code'=>$code,'status'=>'empty','records'=>$records,'rows'=>$rows,'ms'=>$ms,'error'=>null];
                } else {
                    $pass++;
                    $results[$group][] = ['code'=>$code,'status'=>'pass','records'=>$records,'rows'=>$rows,'ms'=>$ms,'error'=>null];
                }
            } catch (\Throwable $e) {
                $fail++;
                $ms = round((microtime(true) - $start) * 1000);
                $results[$group][] = ['code'=>$code,'status'=>'fail','records'=>0,'rows'=>0,'ms'=>$ms,'error'=>$e->getMessage()];
            }
        }
    }

    $total = $pass + $fail + $empty;
    $score = $total > 0 ? round((($pass + $empty) / $total) * 100) : 0;
    $env   = config('app.env');
    $db    = config('database.connections.mysql.database');

    $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Form Generation Test</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:24px}
.header{background:#1e293b;border-radius:12px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
.title{font-size:20px;font-weight:700;color:#f8fafc}.meta{font-size:13px;color:#94a3b8}
.score{font-size:36px;font-weight:800;color:#22c55e}.score.warn{color:#f59e0b}.score.bad{color:#ef4444}
.summary{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
.card{background:#1e293b;border-radius:10px;padding:16px;text-align:center}
.card .num{font-size:28px;font-weight:700}.card .lbl{font-size:12px;color:#94a3b8;margin-top:4px}
.pass .num{color:#22c55e}.fail .num{color:#ef4444}.empty .num{color:#f59e0b}.total .num{color:#60a5fa}
.group{background:#1e293b;border-radius:10px;margin-bottom:16px;overflow:hidden}
.group-title{padding:12px 16px;font-weight:600;font-size:14px;background:#334155;color:#f1f5f9}
.form-row{display:grid;grid-template-columns:180px 80px 80px 80px 60px 1fr;gap:8px;padding:10px 16px;border-bottom:1px solid #0f172a;align-items:center;font-size:13px}
.form-row:last-child{border-bottom:none}
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge.pass{background:#14532d;color:#4ade80}.badge.fail{background:#450a0a;color:#f87171}.badge.empty{background:#451a03;color:#fbbf24}
.err{color:#f87171;font-size:12px;word-break:break-all}.col-hdr{font-size:11px;color:#64748b;font-weight:600}
.params{background:#1e293b;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#94a3b8}
.params a{color:#60a5fa;text-decoration:none;margin-left:12px}
</style></head><body>
HTML;

    $scoreClass = $fail > 0 ? 'bad' : ($empty > 0 ? 'warn' : '');
    $html .= "<div class='header'><div><div class='title'>🧪 Form Generation Test</div><div class='meta'>ENV: {$env} &nbsp;|&nbsp; DB: {$db} &nbsp;|&nbsp; Tenant: {$tenantId} &nbsp;|&nbsp; Branch: {$branchId} &nbsp;|&nbsp; Period: {$month}/{$year}</div></div><div class='score {$scoreClass}'>{$score}%</div></div>";

    $token_q = htmlspecialchars($request->query('token'));
    $html .= "<div class='params'>Change period: ";
    foreach([[12,2025],[11,2025],[1,2026],[12,2024]] as [$m,$y]) {
        $html .= "<a href='?token={$token_q}&tenant_id={$tenantId}&branch_id={$branchId}&month={$m}&year={$y}'>{$m}/{$y}</a>";
    }
    $html .= "&nbsp;&nbsp; Change tenant: ";
    foreach([1,2,3] as $tid) {
        $html .= "<a href='?token={$token_q}&tenant_id={$tid}&branch_id={$tid}&month={$month}&year={$year}'>T{$tid}</a>";
    }
    $html .= "</div>";

    $html .= "<div class='summary'><div class='card total'><div class='num'>{$total}</div><div class='lbl'>Total Forms</div></div><div class='card pass'><div class='num'>{$pass}</div><div class='lbl'>✅ With Data</div></div><div class='card empty'><div class='num'>{$empty}</div><div class='lbl'>⚠️ Empty (No Data)</div></div><div class='card fail'><div class='num'>{$fail}</div><div class='lbl'>❌ Errors</div></div></div>";

    foreach ($results as $group => $rows) {
        $html .= "<div class='group'><div class='group-title'>{$group}</div>";
        $html .= "<div class='form-row'><span class='col-hdr'>FORM CODE</span><span class='col-hdr'>STATUS</span><span class='col-hdr'>RECORDS</span><span class='col-hdr'>ROWS</span><span class='col-hdr'>MS</span><span class='col-hdr'>ERROR</span></div>";
        foreach ($rows as $r) {
            $badge = "<span class='badge {$r['status']}'>{$r['status']}</span>";
            $err   = $r['error'] ? "<span class='err'>{$r['error']}</span>" : '';
            $html .= "<div class='form-row'><span>{$r['code']}</span>{$badge}<span>{$r['records']}</span><span>{$r['rows']}</span><span>{$r['ms']}ms</span>{$err}</div>";
        }
        $html .= "</div>";
    }

    $html .= "</body></html>";
    return response($html)->header('Content-Type','text/html');
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
