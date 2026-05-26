<?php

namespace App\Services\Compliance\Testing;

use App\Models\Branch;
use App\Models\ComplianceBatchForm;
use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceFormsMaster;
use App\Models\Tenant;
use App\Services\Compliance\ComplianceOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        $this->checkStoragePermissions();
        $this->checkRoutes();
        $this->checkControllers();
        $this->checkModels();
        $this->checkServices();
        $this->checkBladeTemplates();
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
        $issues = [];
        $info   = [];

        $required = ['APP_KEY', 'APP_URL', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($required as $key) {
            $val = env($key);
            if (empty($val)) {
                $issues[] = "$key is not set in .env";
            } else {
                $info[$key] = $key === 'APP_KEY' ? '(set)' : $val;
            }
        }

        if (config('app.env') === 'local') {
            $issues[] = 'APP_ENV=local on production — should be production';
        }
        if (config('app.debug') === true) {
            $issues[] = 'APP_DEBUG=true on production — exposes stack traces to users';
        }

        $appUrl = config('app.url');
        if (!str_starts_with($appUrl, 'https://')) {
            $issues[] = "APP_URL ($appUrl) does not use HTTPS";
        }

        $this->record('Environment', $issues, [], $info);
    }

    // ── 2. Database connectivity + critical tables ────────────────────────────

    private function checkDatabase(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        try {
            DB::connection()->getPdo();
            $info['connection'] = 'OK (' . config('database.default') . ')';
        } catch (Throwable $e) {
            $this->record('Database', ["Cannot connect to DB: " . $e->getMessage()]);
            return;
        }

        $criticalTables = [
            'users', 'tenants', 'branches',
            'compliance_execution_batches', 'compliance_batch_forms',
            'compliance_forms_master', 'compliance_sections',
            'compliance_generation_logs', 'compliance_audit_logs',
            'compliance_form_audit_scores', 'compliance_manual_master',
            'compliance_manual_batch_items', 'workforce_employees',
            'workforce_attendance', 'payroll_entries',
        ];

        foreach ($criticalTables as $table) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Missing table: $table";
            }
        }

        // Critical columns
        $columnChecks = [
            'users'                          => ['is_super_admin', 'is_active', 'tenant_id', 'branch_id'],
            'compliance_execution_batches'   => ['user_batch_number', 'branch_id', 'period_month', 'period_year'],
            'compliance_forms_master'        => ['change_summary', 'effective_date', 'version_number', 'source_url'],
            'compliance_batch_forms'         => ['updated_at'],
        ];

        foreach ($columnChecks as $table => $cols) {
            if (!Schema::hasTable($table)) continue;
            foreach ($cols as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    $issues[] = "Missing column: $table.$col";
                }
            }
        }

        // Row counts
        foreach (['tenants', 'branches', 'compliance_forms_master'] as $table) {
            if (Schema::hasTable($table)) {
                $info["$table rows"] = DB::table($table)->count();
            }
        }

        $this->record('Database', $issues, $warnings, $info);
    }

    // ── 3. Pending migrations ─────────────────────────────────────────────────

    private function checkMigrations(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        try {
            $ran = DB::table('migrations')->pluck('migration')->toArray();

            $migrationFiles = collect(File::files(database_path('migrations')))
                ->map(fn($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
                ->toArray();

            $pending = array_diff($migrationFiles, $ran);

            if (count($pending) > 0) {
                foreach ($pending as $m) {
                    $issues[] = "Pending migration: $m";
                }
            }

            $info['total_migrations'] = count($migrationFiles);
            $info['ran']              = count($ran);
            $info['pending']          = count($pending);
        } catch (Throwable $e) {
            $warnings[] = "Could not check migrations: " . $e->getMessage();
        }

        $this->record('Migrations', $issues, $warnings, $info);
    }

    // ── 4. Storage / file permissions ────────────────────────────────────────

    private function checkStoragePermissions(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        $paths = [
            storage_path('logs')                          => 'logs dir',
            storage_path('framework/views')               => 'view cache',
            storage_path('framework/cache')               => 'cache dir',
            storage_path('app/public')                    => 'public storage',
            storage_path('app/compliance_pdfs')           => 'compliance PDFs',
            storage_path('app/compliance_inspection_packs') => 'inspection packs',
            public_path('build')                          => 'Vite build dir',
            public_path('build/manifest.json')            => 'Vite manifest',
        ];

        foreach ($paths as $path => $label) {
            if (!file_exists($path)) {
                $issues[] = "Missing: $label ($path)";
            } elseif (is_dir($path) && !is_writable($path)) {
                $issues[] = "Not writable: $label";
            } else {
                $info[$label] = 'OK';
            }
        }

        // Check log file size
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $sizeMb = round(filesize($logFile) / 1024 / 1024, 2);
            $info['log file size'] = "{$sizeMb} MB";
            if ($sizeMb > 50) {
                $warnings[] = "Log file is {$sizeMb} MB — consider rotating";
            }
        }

        $this->record('Storage & Files', $issues, $warnings, $info);
    }

    // ── 5. Routes ─────────────────────────────────────────────────────────────

    private function checkRoutes(): void
    {
        $issues  = [];
        $info    = [];

        $required = [
            'compliance.dashboard', 'compliance.batch.create',
            'batch.process.next', 'compliance.batch.preview',
            'compliance.batch.inspectionPack', 'compliance.batch.form.pdf',
            'super-admin.dashboard', 'super-admin.analytics',
            'login', 'logout',
        ];

        foreach ($required as $name) {
            if (Route::has($name)) {
                $info[$name] = 'registered';
            } else {
                $issues[] = "Route not registered: $name";
            }
        }

        $this->record('Routes', $issues, [], $info);
    }

    // ── 6. Controllers ────────────────────────────────────────────────────────

    private function checkControllers(): void
    {
        $issues = [];
        $info   = [];

        $controllers = [
            app_path('Http/Controllers/BatchProcessingController.php'),
            app_path('Http/Controllers/ComplianceDashboardController.php'),
            app_path('Http/Controllers/ComplianceExecutionController.php'),
            app_path('Http/Controllers/DataInputController.php'),
            app_path('Http/Controllers/ManualComplianceController.php'),
            app_path('Http/Controllers/ManualComplianceExecutionController.php'),
            app_path('Http/Controllers/SuperAdmin/SuperAdminController.php'),
            app_path('Http/Controllers/SuperAdmin/DashboardController.php'),
            app_path('Http/Controllers/Compliance/CompliancePreviewController.php'),
        ];

        foreach ($controllers as $path) {
            $name = basename($path);
            if (file_exists($path)) {
                // Try to parse for syntax errors
                $output = null;
                exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $code);
                if ($code !== 0) {
                    $issues[] = "Syntax error in $name: " . implode(' ', $output);
                } else {
                    $info[$name] = 'OK';
                }
            } else {
                $issues[] = "Missing: $name";
            }
        }

        $this->record('Controllers', $issues, [], $info);
    }

    // ── 7. Models ─────────────────────────────────────────────────────────────

    private function checkModels(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        $models = [
            \App\Models\Tenant::class,
            \App\Models\Branch::class,
            \App\Models\User::class,
            \App\Models\ComplianceExecutionBatch::class,
            \App\Models\ComplianceBatchForm::class,
            \App\Models\ComplianceFormsMaster::class,
            \App\Models\ComplianceAuditLog::class,
            \App\Models\AuditLog::class,
            \App\Models\ComplianceGenerationLog::class,
            \App\Models\ManualComplianceMaster::class,
            \App\Models\ManualComplianceBatchItem::class,
        ];

        foreach ($models as $class) {
            $short = class_basename($class);
            try {
                $instance = new $class();
                $table    = $instance->getTable();
                if (Schema::hasTable($table)) {
                    $info[$short] = "table: $table ✓";
                } else {
                    $issues[] = "$short → table '$table' does not exist on DB";
                }
            } catch (Throwable $e) {
                $issues[] = "$short failed to instantiate: " . $e->getMessage();
            }
        }

        $this->record('Models', $issues, $warnings, $info);
    }

    // ── 8. Services ───────────────────────────────────────────────────────────

    private function checkServices(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        $servicePaths = [
            app_path('Services/Compliance/ComplianceOrchestrator.php')         => 'ComplianceOrchestrator',
            app_path('Services/Compliance/FormApis/BaseFormApiService.php')    => 'BaseFormApiService',
            app_path('Services/Compliance/FormApis/FormApiServiceFactory.php') => 'FormApiServiceFactory',
            app_path('Services/Compliance/Audit/ComplianceAuditService.php')   => 'ComplianceAuditService',
        ];

        foreach ($servicePaths as $path => $label) {
            if (file_exists($path)) {
                $info[$label] = 'found';
            } else {
                $issues[] = "Missing service: $label";
            }
        }

        // Count API services
        $apiDir = app_path('Services/Compliance/FormApis');
        if (is_dir($apiDir)) {
            $count = count(array_filter(
                File::files($apiDir),
                fn($f) => str_ends_with($f->getFilename(), 'ApiService.php')
                       && !in_array($f->getFilename(), ['BaseFormApiService.php', 'FormApiServiceFactory.php'])
            ));
            $info['API services found'] = $count;
            if ($count < 30) {
                $warnings[] = "Only $count API services found (expected ~34)";
            }
        }

        // Count generators
        $genDir = app_path('Services/Compliance/FormGenerator');
        if (is_dir($genDir)) {
            $count = count(array_filter(
                File::files($genDir),
                fn($f) => str_ends_with($f->getFilename(), 'Generator.php')
                       && $f->getFilename() !== 'BaseFormGenerator.php'
            ));
            $info['Form generators found'] = $count;
        }

        $this->record('Services', $issues, $warnings, $info);
    }

    // ── 9. Blade templates ────────────────────────────────────────────────────

    private function checkBladeTemplates(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        $viewDirs = [
            resource_path('views/compliance/forms')   => 'compliance/forms',
            resource_path('views/compliance/partials') => 'compliance/partials',
            resource_path('views/super-admin')         => 'super-admin',
        ];

        foreach ($viewDirs as $dir => $label) {
            if (!is_dir($dir)) {
                $issues[] = "Missing view directory: $label";
                continue;
            }
            $files = File::allFiles($dir);
            $info["$label views"] = count($files);
        }

        // Check critical views exist
        $criticalViews = [
            resource_path('views/compliance/layouts/app.blade.php'),
            resource_path('views/compliance/dashboard.blade.php'),
            resource_path('views/super-admin/sa-dashboard.blade.php'),
            resource_path('views/super-admin/layout.blade.php'),
        ];

        foreach ($criticalViews as $path) {
            if (!file_exists($path)) {
                $issues[] = "Missing critical view: " . str_replace(resource_path('views/'), '', $path);
            }
        }

        $this->record('Blade Templates', $issues, $warnings, $info);
    }

    // ── 10. Laravel log — last 50 errors ─────────────────────────────────────

    private function checkLaravelLog(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            $warnings[] = 'laravel.log not found';
            $this->record('Laravel Log', $issues, $warnings, $info);
            return;
        }

        // Read last 300 lines efficiently
        $file = new \SplFileObject($logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $total = $file->key();
        $start = max(0, $total - 300);
        $file->seek($start);

        $lines      = [];
        $errorLines = [];

        while (!$file->eof()) {
            $line = rtrim($file->current());
            $file->next();
            if ($line === '') continue;
            $lines[] = $line;
            if (preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY)/', $line)) {
                $errorLines[] = $line;
            }
        }

        // Group unique error messages
        $uniqueErrors = [];
        foreach ($errorLines as $line) {
            if (preg_match('/\] \w+\.(?:ERROR|CRITICAL): (.+?)(?:\s*\{|$)/', $line, $m)) {
                $msg = trim($m[1]);
                $uniqueErrors[$msg] = ($uniqueErrors[$msg] ?? 0) + 1;
            }
        }

        arsort($uniqueErrors);

        $info['total_error_lines_in_last_300'] = count($errorLines);
        $info['unique_error_types']            = count($uniqueErrors);

        foreach (array_slice($uniqueErrors, 0, 15, true) as $msg => $cnt) {
            $issues[] = "[{$cnt}x] $msg";
        }

        // Surface the last 20 raw log lines for context
        $info['last_20_log_lines'] = array_slice($lines, -20);

        $this->record('Laravel Log (last 300 lines)', $issues, $warnings, $info);
    }

    // ── 11. Orchestrator smoke test ───────────────────────────────────────────

    private function checkOrchestrator(): void
    {
        $issues   = [];
        $warnings = [];
        $info     = [];

        $tenant = Tenant::first();
        if (!$tenant) {
            $warnings[] = 'No tenant in DB — skipping orchestrator smoke test';
            $this->record('Orchestrator Smoke Test', $issues, $warnings, $info);
            return;
        }

        $branch = Branch::where('tenant_id', $tenant->id)->first();
        if (!$branch) {
            $warnings[] = "No branch for tenant #{$tenant->id}";
            $this->record('Orchestrator Smoke Test', $issues, $warnings, $info);
            return;
        }

        // Pick first active form
        $form = ComplianceFormsMaster::where('is_active', true)->first();
        if (!$form) {
            $warnings[] = 'No active forms in compliance_forms_master';
            $this->record('Orchestrator Smoke Test', $issues, $warnings, $info);
            return;
        }

        try {
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
            $info['time_ms']   = $ms;
            $info['status']    = $result['status'];

            if ($result['status'] !== 'success') {
                $issues[] = "Orchestrator returned non-success for {$form->form_code}: " . ($result['error'] ?? 'unknown');
            }
        } catch (Throwable $e) {
            $issues[] = "Orchestrator threw: " . $e->getMessage();
            $info['trace_hint'] = $e->getFile() . ':' . $e->getLine();
        }

        $this->record('Orchestrator Smoke Test', $issues, $warnings, $info);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function record(string $name, array $errors, array $warnings = [], array $info = []): void
    {
        $status = count($errors) > 0 ? 'error' : (count($warnings) > 0 ? 'warning' : 'pass');
        $this->results[$name] = compact('status', 'errors', 'warnings', 'info');
    }
}
