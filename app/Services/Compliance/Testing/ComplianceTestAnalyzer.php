<?php

namespace App\Services\Compliance\Testing;

use App\Models\Branch;
use App\Models\ComplianceFormsMaster;
use App\Models\Tenant;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ComplianceTestAnalyzer
{
    private array $results = [];

    public function __construct(private ComplianceOrchestrator $orchestrator) {}

    public function runFullAnalysis(): array
    {
        $start = microtime(true);

        $this->checkEnv();
        $this->checkDatabase();
        $this->checkMigrations();
        $this->checkStorage();
        $this->checkRoutes();
        $this->checkControllerFiles();
        $this->checkServiceFiles();
        $this->checkViewFiles();
        $this->checkLaravelLog();
        $this->checkOrchestrator();

        $passed   = count(array_filter($this->results, fn($r) => $r['status'] === 'pass'));
        $warnings = count(array_filter($this->results, fn($r) => $r['status'] === 'warning'));
        $errors   = count(array_filter($this->results, fn($r) => $r['status'] === 'error'));
        $total    = count($this->results);

        return [
            'execution_time_ms' => round((microtime(true) - $start) * 1000),
            'health_score'      => $total > 0 ? (int)(($passed * 100 + $warnings * 60) / $total) : 0,
            'summary'           => compact('passed', 'warnings', 'errors', 'total'),
            'checks'            => $this->results,
            'timestamp'         => now()->toDateTimeString(),
        ];
    }

    // ── 1. Environment ────────────────────────────────────────────────────────

    private function checkEnv(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            // Use config() not env() — env() returns null when config is cached
            foreach ([
                'APP_KEY'      => config('app.key'),
                'APP_URL'      => config('app.url'),
                'DB_HOST'      => config('database.connections.mysql.host'),
                'DB_DATABASE'  => config('database.connections.mysql.database'),
                'DB_USERNAME'  => config('database.connections.mysql.username'),
            ] as $k => $v) {
                if (empty($v)) $errors[] = "$k is not set";
                else $info[$k] = $k === 'APP_KEY' ? '(set ✓)' : $v;
            }
            if (config('app.env') === 'local')   $errors[]   = 'APP_ENV=local — should be production';
            if (config('app.debug') === true)     $errors[]   = 'APP_DEBUG=true — exposes stack traces';
            if (!str_starts_with((string)config('app.url'), 'https://'))
                $warnings[] = 'APP_URL does not use HTTPS: ' . config('app.url');

            $info['php_version']  = PHP_VERSION;
            $info['laravel']      = app()->version();
            $info['timezone']     = config('app.timezone');
            $info['ops_token_set'] = config('app.ops_token') ? 'yes' : 'NO — /_ops routes unprotected!';
        } catch (Throwable $e) { $errors[] = 'Check failed: ' . $e->getMessage(); }
        $this->record('Environment', $errors, $warnings, $info);
    }

    // ── 2. Database ───────────────────────────────────────────────────────────

    private function checkDatabase(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            DB::connection()->getPdo();
            $info['connection'] = config('database.default') . ' ✓';
        } catch (Throwable $e) {
            $this->record('Database', ['Cannot connect: ' . $e->getMessage()]);
            return;
        }

        $tables = [
            'users', 'tenants', 'branches',
            'compliance_execution_batches', 'compliance_batch_forms',
            'compliance_forms_master', 'compliance_sections',
            'compliance_generation_logs', 'compliance_audit_logs',
            'compliance_form_audit_scores', 'compliance_manual_master',
            'compliance_manual_batch_items', 'workforce_employee',
            'workforce_attendance', 'payroll_entries', 'bonus_records',
        ];

        foreach ($tables as $t) {
            try {
                if (!Schema::hasTable($t)) $errors[] = "Missing table: $t";
                else $info[$t] = DB::table($t)->count() . ' rows';
            } catch (Throwable $e) { $errors[] = "Error checking $t: " . $e->getMessage(); }
        }

        // Critical columns
        $cols = [
            'users'                        => ['is_super_admin', 'is_active', 'tenant_id', 'branch_id'],
            'compliance_execution_batches' => ['user_batch_number', 'branch_id', 'period_month', 'period_year'],
            'compliance_forms_master'      => ['change_summary', 'effective_date', 'version_number'],
            'compliance_batch_forms'       => ['updated_at', 'status', 'file_path'],
        ];
        foreach ($cols as $table => $columns) {
            try {
                if (!Schema::hasTable($table)) continue;
                foreach ($columns as $col) {
                    if (!Schema::hasColumn($table, $col))
                        $errors[] = "Missing column: $table.$col";
                }
            } catch (Throwable $e) { $warnings[] = "Column check failed for $table: " . $e->getMessage(); }
        }

        $this->record('Database', $errors, $warnings, $info);
    }

    // ── 3. Migrations ─────────────────────────────────────────────────────────

    private function checkMigrations(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            $ran   = DB::table('migrations')->pluck('migration')->toArray();
            $files = array_map(
                fn($f) => pathinfo($f, PATHINFO_FILENAME),
                glob(database_path('migrations/*.php')) ?: []
            );
            $pending = array_diff($files, $ran);

            $info['total_files'] = count($files);
            $info['ran']         = count($ran);
            $info['pending']     = count($pending);

            foreach ($pending as $m) $errors[] = "Pending migration: $m";
        } catch (Throwable $e) { $warnings[] = 'Could not check migrations: ' . $e->getMessage(); }
        $this->record('Migrations', $errors, $warnings, $info);
    }

    // ── 4. Storage & files ────────────────────────────────────────────────────

    private function checkStorage(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            $checks = [
                storage_path('logs')                            => 'logs dir',
                storage_path('framework/views')                 => 'view cache dir',
                storage_path('framework/cache')                 => 'cache dir',
                storage_path('app/public')                      => 'public storage',
                storage_path('app/compliance_pdfs')             => 'compliance PDFs dir',
                storage_path('app/compliance_inspection_packs') => 'inspection packs dir',
                public_path('build')                            => 'Vite build dir',
                public_path('build/manifest.json')              => 'Vite manifest.json',
            ];

            foreach ($checks as $path => $label) {
                if (!file_exists($path))          $errors[]   = "Missing: $label";
                elseif (is_dir($path) && !is_writable($path)) $errors[] = "Not writable: $label";
                else                              $info[$label] = 'OK';
            }

            $log = storage_path('logs/laravel.log');
            if (file_exists($log)) {
                $mb = round(filesize($log) / 1048576, 2);
                $info['laravel.log size'] = "{$mb} MB";
                if ($mb > 50) $warnings[] = "Log file is {$mb} MB — consider rotating";
            }
        } catch (Throwable $e) { $warnings[] = 'Storage check failed: ' . $e->getMessage(); }
        $this->record('Storage & Files', $errors, $warnings, $info);
    }

    // ── 5. Routes ─────────────────────────────────────────────────────────────

    private function checkRoutes(): void
    {
        $errors = []; $info = [];
        try {
            $required = [
                'compliance.dashboard', 'compliance.batch.create',
                'batch.process.next', 'compliance.batch.preview',
                'compliance.batch.inspectionPack', 'compliance.batch.form.pdf',
                'super-admin.dashboard', 'super-admin.analytics',
                'login', 'logout',
            ];
            foreach ($required as $name) {
                if (Route::has($name)) $info[$name] = 'registered ✓';
                else $errors[] = "Route not registered: $name";
            }
        } catch (Throwable $e) { $errors[] = 'Route check failed: ' . $e->getMessage(); }
        $this->record('Routes', $errors, [], $info);
    }

    // ── 6. Controller files ───────────────────────────────────────────────────

    private function checkControllerFiles(): void
    {
        $errors = []; $info = [];
        try {
            $files = [
                'BatchProcessingController'                    => app_path('Http/Controllers/BatchProcessingController.php'),
                'ComplianceExecutionController'                => app_path('Http/Controllers/ComplianceExecutionController.php'),
                'ComplianceDashboardController'                => app_path('Http/Controllers/ComplianceDashboardController.php'),
                'DataInputController'                          => app_path('Http/Controllers/DataInputController.php'),
                'ManualComplianceController'                   => app_path('Http/Controllers/ManualComplianceController.php'),
                'ManualComplianceExecutionController'          => app_path('Http/Controllers/ManualComplianceExecutionController.php'),
                'SuperAdmin/SuperAdminController'              => app_path('Http/Controllers/SuperAdmin/SuperAdminController.php'),
                'SuperAdmin/DashboardController'               => app_path('Http/Controllers/SuperAdmin/DashboardController.php'),
                'Compliance/CompliancePreviewController'       => app_path('Http/Controllers/Compliance/CompliancePreviewController.php'),
                'Compliance/ComplianceTestAnalysisController'  => app_path('Http/Controllers/Compliance/ComplianceTestAnalysisController.php'),
            ];
            foreach ($files as $name => $path) {
                if (file_exists($path)) $info[$name] = 'found ✓';
                else $errors[] = "Missing controller: $name";
            }
        } catch (Throwable $e) { $errors[] = 'Controller check failed: ' . $e->getMessage(); }
        $this->record('Controllers', $errors, [], $info);
    }

    // ── 7. Service files ──────────────────────────────────────────────────────

    private function checkServiceFiles(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            $required = [
                'ComplianceOrchestrator'  => app_path('Services/Compliance/ComplianceOrchestrator.php'),
                'BaseFormApiService'      => app_path('Services/Compliance/FormApis/BaseFormApiService.php'),
                'FormApiServiceFactory'   => app_path('Services/Compliance/FormApis/FormApiServiceFactory.php'),
                'ComplianceAuditService'  => app_path('Services/Compliance/Audit/ComplianceAuditService.php'),
            ];
            foreach ($required as $name => $path) {
                if (file_exists($path)) $info[$name] = 'found ✓';
                else $errors[] = "Missing service: $name";
            }

            // Count API services
            $apiDir = app_path('Services/Compliance/FormApis');
            if (is_dir($apiDir)) {
                $count = count(array_filter(
                    glob($apiDir . '/*ApiService.php') ?: [],
                    fn($f) => !in_array(basename($f), ['BaseFormApiService.php', 'FormApiServiceFactory.php'])
                ));
                $info['API services'] = $count;
                if ($count < 30) $warnings[] = "Only $count API services found (expected ~34)";
            } else {
                $errors[] = 'FormApis directory missing';
            }

            // Count generators
            $genDir = app_path('Services/Compliance/FormGenerator');
            if (is_dir($genDir)) {
                $count = count(array_filter(
                    glob($genDir . '/*Generator.php') ?: [],
                    fn($f) => basename($f) !== 'BaseFormGenerator.php'
                ));
                $info['Form generators'] = $count;
            } else {
                $errors[] = 'FormGenerator directory missing';
            }

            // Count blade form templates
            $tplDir = resource_path('views/compliance/forms');
            if (is_dir($tplDir)) {
                $info['Form blade templates'] = count(glob($tplDir . '/*.blade.php') ?: []);
            } else {
                $errors[] = 'views/compliance/forms directory missing';
            }
        } catch (Throwable $e) { $errors[] = 'Service check failed: ' . $e->getMessage(); }
        $this->record('Services & Generators', $errors, $warnings, $info);
    }

    // ── 8. Critical view files ────────────────────────────────────────────────

    private function checkViewFiles(): void
    {
        $errors = []; $info = [];
        try {
            $views = [
                'compliance/layouts/app'          => resource_path('views/compliance/layouts/app.blade.php'),
                'compliance/dashboard'             => resource_path('views/compliance/dashboard.blade.php'),
                'super-admin/layout'               => resource_path('views/super-admin/layout.blade.php'),
                'super-admin/sa-dashboard'         => resource_path('views/super-admin/sa-dashboard.blade.php'),
                'super-admin/dashboard'            => resource_path('views/super-admin/dashboard.blade.php'),
                'compliance/partials/org-info'     => resource_path('views/compliance/partials/org-info.blade.php'),
                'compliance/partials/recent-batches' => resource_path('views/compliance/partials/recent-batches.blade.php'),
            ];
            foreach ($views as $name => $path) {
                if (file_exists($path)) $info[$name] = 'found ✓';
                else $errors[] = "Missing view: $name";
            }
        } catch (Throwable $e) { $errors[] = 'View check failed: ' . $e->getMessage(); }
        $this->record('Views', $errors, [], $info);
    }

    // ── 9. Laravel log analysis ───────────────────────────────────────────────

    private function checkLaravelLog(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                $warnings[] = 'laravel.log not found';
                $this->record('Laravel Log', $errors, $warnings, $info);
                return;
            }

            // Read last 400 lines efficiently
            $file = new \SplFileObject($logFile, 'r');
            $file->seek(PHP_INT_MAX);
            $total = $file->key();
            $file->seek(max(0, $total - 400));

            $lines = $errorLines = [];
            while (!$file->eof()) {
                $line = rtrim((string)$file->current());
                $file->next();
                if ($line === '') continue;
                $lines[] = $line;
                if (preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY)/', $line))
                    $errorLines[] = $line;
            }

            // Deduplicate and count
            $unique = [];
            foreach ($errorLines as $line) {
                if (preg_match('/\] \w+\.\w+: (.+?)(?:\s*\{|\s*\[|$)/', $line, $m)) {
                    $msg = trim($m[1]);
                    $unique[$msg] = ($unique[$msg] ?? 0) + 1;
                }
            }
            arsort($unique);

            $info['error_lines_in_last_400'] = count($errorLines);
            $info['unique_error_types']       = count($unique);

            foreach (array_slice($unique, 0, 20, true) as $msg => $cnt)
                $errors[] = "[{$cnt}x] $msg";

            $info['last_30_log_lines'] = array_slice($lines, -30);
        } catch (Throwable $e) { $warnings[] = 'Log analysis failed: ' . $e->getMessage(); }
        $this->record('Laravel Log (last 400 lines)', $errors, $warnings, $info);
    }

    // ── 10. Orchestrator smoke test ───────────────────────────────────────────

    private function checkOrchestrator(): void
    {
        $errors = []; $warnings = []; $info = [];
        try {
            $tenant = Tenant::first();
            if (!$tenant) { $warnings[] = 'No tenant in DB — skipping'; $this->record('Orchestrator Smoke Test', $errors, $warnings, $info); return; }

            $branch = Branch::where('tenant_id', $tenant->id)->first();
            if (!$branch) { $warnings[] = 'No branch found — skipping'; $this->record('Orchestrator Smoke Test', $errors, $warnings, $info); return; }

            $form = ComplianceFormsMaster::where('is_active', true)->first();
            if (!$form) { $warnings[] = 'No active forms in DB — skipping'; $this->record('Orchestrator Smoke Test', $errors, $warnings, $info); return; }

            $t0     = microtime(true);
            $result = $this->orchestrator->execute(
                $tenant->id, $branch->id,
                now()->month, now()->year,
                $form->form_code, 'preview'
            );
            $ms = round((microtime(true) - $t0) * 1000);

            $info['tenant']    = $tenant->name;
            $info['branch']    = $branch->branch_name;
            $info['form_code'] = $form->form_code;
            $info['time_ms']   = "{$ms}ms";
            $info['result']    = $result['status'];

            if ($result['status'] !== 'success')
                $errors[] = "Orchestrator returned '{$result['status']}' for {$form->form_code}: " . ($result['error'] ?? 'no error message');
        } catch (Throwable $e) {
            $errors[] = get_class($e) . ': ' . $e->getMessage();
            $errors[] = 'at ' . $e->getFile() . ':' . $e->getLine();
        }
        $this->record('Orchestrator Smoke Test', $errors, $warnings, $info);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function record(string $name, array $errors, array $warnings = [], array $info = []): void
    {
        $status = count($errors) > 0 ? 'error' : (count($warnings) > 0 ? 'warning' : 'pass');
        $this->results[$name] = compact('status', 'errors', 'warnings', 'info');
    }
}
