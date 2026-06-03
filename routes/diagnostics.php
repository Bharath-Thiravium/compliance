<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

Route::get('/_full-diagnostic', function () {
    $report = [];
    
    // 1. Basic Environment
    $report['environment'] = [
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'app_url' => config('app.url'),
        'timestamp' => now()->toDateTimeString(),
    ];
    
    // 2. File System Check
    $report['file_system'] = [
        'routes_web' => file_exists(base_path('routes/web.php')) ? 'EXISTS' : 'MISSING',
        'routes_compliance' => file_exists(base_path('routes/compliance.php')) ? 'EXISTS' : 'MISSING',
        'routes_batch' => file_exists(base_path('routes/batch-processing.php')) ? 'EXISTS' : 'MISSING',
        'routes_data' => file_exists(base_path('routes/data-input.php')) ? 'EXISTS' : 'MISSING',
        'routes_super_admin' => file_exists(base_path('routes/super-admin.php')) ? 'EXISTS' : 'MISSING',
        'routes_smart' => file_exists(base_path('routes/smart-templates.php')) ? 'EXISTS' : 'MISSING',
        'app_http_kernel' => file_exists(app_path('Http/Kernel.php')) ? 'EXISTS' : 'MISSING',
        'app_service_provider' => file_exists(app_path('Providers/AppServiceProvider.php')) ? 'EXISTS' : 'MISSING',
        'bootstrap_app' => file_exists(base_path('bootstrap/app.php')) ? 'EXISTS' : 'MISSING',
    ];
    
    // 3. Route Count
    try {
        $routes = app('router')->getRoutes();
        $report['routes'] = [
            'total_count' => count($routes),
            'routes_by_method' => [
                'GET' => count(array_filter($routes, fn($r) => in_array('GET', $r->methods))),
                'POST' => count(array_filter($routes, fn($r) => in_array('POST', $r->methods))),
                'PUT' => count(array_filter($routes, fn($r) => in_array('PUT', $r->methods))),
                'DELETE' => count(array_filter($routes, fn($r) => in_array('DELETE', $r->methods))),
                'PATCH' => count(array_filter($routes, fn($r) => in_array('PATCH', $r->methods))),
            ],
            'login_route_exists' => $routes->getByName('login') ? 'YES' : 'NO',
            'compliance_routes_count' => count(array_filter($routes, fn($r) => str_contains($r->uri, 'compliance'))),
        ];
    } catch (\Throwable $e) {
        $report['routes']['error'] = $e->getMessage();
    }
    
    // 4. Middleware Check
    $kernel = app('Illuminate\Contracts\Http\Kernel');
    $report['middleware'] = [
        'global_middleware_count' => count($kernel->middleware),
        'route_middleware_count' => count($kernel->routeMiddleware),
        'has_web_middleware' => isset($kernel->routeMiddleware['web']) ? 'YES' : 'NO',
        'has_auth_middleware' => isset($kernel->routeMiddleware['auth']) ? 'YES' : 'NO',
    ];
    
    // 5. Database Check
    try {
        $canConnect = DB::connection()->getPdo() !== null;
        $report['database'] = [
            'connected' => $canConnect ? 'YES' : 'NO',
            'driver' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'database' => config('database.connections.mysql.database'),
            'tables_count' => count(DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", [config('database.connections.mysql.database')])),
        ];
    } catch (\Throwable $e) {
        $report['database']['error'] = $e->getMessage();
    }
    
    // 6. Service Providers
    $report['service_providers'] = [
        'loaded_count' => count(app()->getLoadedProviders()),
        'providers' => array_keys(app()->getLoadedProviders()),
    ];
    
    // 7. Cache Configuration
    $report['cache'] = [
        'driver' => config('cache.default'),
        'cache_path' => storage_path('framework/cache'),
        'cache_path_exists' => is_dir(storage_path('framework/cache')) ? 'YES' : 'NO',
        'is_cache_enabled' => config('cache.default') !== 'null' ? 'YES' : 'NO',
    ];
    
    // 8. Session Configuration
    $report['session'] = [
        'driver' => config('session.driver'),
        'cookie_name' => config('session.cookie'),
        'lifetime' => config('session.lifetime'),
    ];
    
    // 9. Auth Configuration
    $report['auth'] = [
        'default_guard' => config('auth.defaults.guard'),
        'default_provider' => config('auth.defaults.provider'),
        'user_model' => config('auth.providers.users.model'),
        'user_table' => config('auth.providers.users.table'),
    ];
    
    // 10. Important Controllers Existence
    $controllers = [
        'AuthController' => app_path('Http/Controllers/AuthController.php'),
        'ComplianceExecutionController' => app_path('Http/Controllers/ComplianceExecutionController.php'),
        'BatchProcessingController' => app_path('Http/Controllers/BatchProcessingController.php'),
        'DataInputController' => app_path('Http/Controllers/DataInputController.php'),
        'SuperAdmin\\SuperAdminController' => app_path('Http/Controllers/SuperAdmin/SuperAdminController.php'),
    ];
    
    $report['controllers'] = [];
    foreach ($controllers as $name => $path) {
        $report['controllers'][$name] = file_exists($path) ? 'EXISTS' : 'MISSING';
    }
    
    // 11. Key Services
    $services = [
        'ComplianceOrchestrator' => app_path('Services/Compliance/ComplianceOrchestrator.php'),
        'ComplianceTestAnalyzer' => app_path('Services/Compliance/Testing/ComplianceTestAnalyzer.php'),
        'FormApiServiceFactory' => app_path('Services/Compliance/FormApis/FormApiServiceFactory.php'),
        'FormGeneratorFactory' => app_path('Services/Compliance/FormGenerator/FormGeneratorFactory.php'),
    ];
    
    $report['services'] = [];
    foreach ($services as $name => $path) {
        $report['services'][$name] = file_exists($path) ? 'EXISTS' : 'MISSING';
    }
    
    // 12. Autoloader Check
    $report['autoloader'] = [
        'vendor_autoload' => file_exists(base_path('vendor/autoload.php')) ? 'EXISTS' : 'MISSING',
        'composer_json' => file_exists(base_path('composer.json')) ? 'EXISTS' : 'MISSING',
        'composer_lock' => file_exists(base_path('composer.lock')) ? 'EXISTS' : 'MISSING',
    ];
    
    // 13. Route Guard Check
    try {
        $allRoutes = app('router')->getRoutes();
        $report['route_analysis'] = [
            'total_routes' => count($allRoutes),
            'routes_with_auth' => count(array_filter($allRoutes, fn($r) => collect($r->middleware)->contains('auth'))),
            'routes_with_web' => count(array_filter($allRoutes, fn($r) => collect($r->middleware)->contains('web'))),
            'routes_with_super_admin' => count(array_filter($allRoutes, fn($r) => collect($r->middleware)->contains('super.admin'))),
        ];
    } catch (\Throwable $e) {
        $report['route_analysis']['error'] = $e->getMessage();
    }
    
    // 14. Middleware Aliases
    $report['middleware_aliases'] = array_keys($kernel->routeMiddleware);
    
    // 15. Configuration Cache
    try {
        $report['config_cache'] = [
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')) ? 'YES' : 'NO',
            'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'YES' : 'NO',
            'services_cached' => file_exists(base_path('bootstrap/cache/services.php')) ? 'YES' : 'NO',
        ];
    } catch (\Throwable $e) {
        $report['config_cache']['error'] = $e->getMessage();
    }
    
    // 16. Stored Routes Sample
    try {
        $allRoutes = app('router')->getRoutes();
        $report['sample_routes'] = [];
        
        foreach (array_slice($allRoutes, 0, 20) as $route) {
            $report['sample_routes'][] = [
                'method' => implode('|', $route->methods),
                'uri' => $route->uri,
                'name' => $route->getName() ?? 'unnamed',
                'middleware' => implode(',', $route->middleware),
            ];
        }
    } catch (\Throwable $e) {
        $report['sample_routes']['error'] = $e->getMessage();
    }
    
    // 17. HTTP Kernel Middleware Stack
    $report['kernel_middleware'] = [
        'global' => $kernel->middleware,
        'route_middleware_names' => array_keys($kernel->routeMiddleware),
        'middleware_groups' => array_keys($kernel->middlewareGroups),
    ];
    
    // 18. Application Check
    $report['app_state'] = [
        'is_running' => app()->isRunning() ? 'YES' : 'NO',
        'is_booted' => app()->isBooted() ? 'YES' : 'NO',
        'is_configured' => app()->isConfigured ? 'YES' : 'NO',
        'locale' => config('app.locale'),
        'timezone' => config('app.timezone'),
    ];
    
    // 19. Error Logs Sample
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $lines = array_slice(file($logFile), -50);
        $report['recent_logs'] = array_map('trim', $lines);
    } else {
        $report['recent_logs'] = 'Log file not found';
    }
    
    // 20. User Model & Authentication
    try {
        $userModel = config('auth.providers.users.model');
        $report['auth_models'] = [
            'user_model' => $userModel,
            'model_exists' => class_exists($userModel) ? 'YES' : 'NO',
            'users_table_exists' => Schema::hasTable('users') ? 'YES' : 'NO',
            'users_count' => Schema::hasTable('users') ? DB::table('users')->count() : 0,
        ];
    } catch (\Throwable $e) {
        $report['auth_models']['error'] = $e->getMessage();
    }
    
    // 21. Compliance Routes Detailed
    try {
        $allRoutes = app('router')->getRoutes();
        $complianceRoutes = array_filter($allRoutes, fn($r) => str_contains($r->uri, 'compliance'));
        $report['compliance_routes_sample'] = [];
        
        foreach (array_slice($complianceRoutes, 0, 15) as $route) {
            $report['compliance_routes_sample'][] = [
                'uri' => $route->uri,
                'name' => $route->getName(),
                'methods' => implode(',', $route->methods),
            ];
        }
    } catch (\Throwable $e) {
        $report['compliance_routes_sample']['error'] = $e->getMessage();
    }
    
    // 22. Request/Response Check
    try {
        $request = request();
        $report['request_info'] = [
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->url(),
            'host' => $request->host(),
            'is_secure' => $request->isSecure() ? 'HTTPS' : 'HTTP',
            'user_agent' => substr($request->userAgent(), 0, 100),
        ];
    } catch (\Throwable $e) {
        $report['request_info']['error'] = $e->getMessage();
    }
    
    return response()->json($report, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->withoutMiddleware('web');
