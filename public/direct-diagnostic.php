<?php
/**
 * Direct Diagnostic Tool - Accesses Laravel from public folder
 * Usage: https://athenas.co.in/compliance/ce/public/direct-diagnostic.php
 */

try {
    // Bootstrap Laravel
    require_once __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    // Get all diagnostic information
    $report = [];
    
    // 1. Basic Environment
    $report['environment'] = [
        'php_version' => PHP_VERSION,
        'laravel_version' => $app->version(),
        'app_env' => $app['config']['app.env'],
        'app_debug' => $app['config']['app.debug'],
        'app_url' => $app['config']['app.url'],
        'timestamp' => date('Y-m-d H:i:s'),
    ];
    
    // 2. File System Check
    $baseDir = __DIR__.'/..';
    $report['file_system'] = [
        'routes_web' => file_exists("$baseDir/routes/web.php") ? 'EXISTS' : 'MISSING',
        'routes_compliance' => file_exists("$baseDir/routes/compliance.php") ? 'EXISTS' : 'MISSING',
        'routes_batch' => file_exists("$baseDir/routes/batch-processing.php") ? 'EXISTS' : 'MISSING',
        'routes_data' => file_exists("$baseDir/routes/data-input.php") ? 'EXISTS' : 'MISSING',
        'routes_super_admin' => file_exists("$baseDir/routes/super-admin.php") ? 'EXISTS' : 'MISSING',
        'routes_smart' => file_exists("$baseDir/routes/smart-templates.php") ? 'EXISTS' : 'MISSING',
        'app_http_kernel' => file_exists("$baseDir/app/Http/Kernel.php") ? 'EXISTS' : 'MISSING',
        'bootstrap_app' => file_exists("$baseDir/bootstrap/app.php") ? 'EXISTS' : 'MISSING',
        'public_index' => file_exists("$baseDir/public/index.php") ? 'EXISTS' : 'MISSING',
    ];
    
    // 3. Route Count
    try {
        $router = $app['router'];
        $routes = $router->getRoutes();
        $report['routes'] = [
            'total_count' => count($routes),
            'routes_by_method' => [
                'GET' => count(array_filter($routes, fn($r) => in_array('GET', $r->methods))),
                'POST' => count(array_filter($routes, fn($r) => in_array('POST', $r->methods))),
                'PUT' => count(array_filter($routes, fn($r) => in_array('PUT', $r->methods))),
                'DELETE' => count(array_filter($routes, fn($r) => in_array('DELETE', $r->methods))),
            ],
            'login_route_exists' => $routes->getByName('login') ? 'YES' : 'NO',
            'compliance_routes_count' => count(array_filter($routes, fn($r) => str_contains($r->uri, 'compliance'))),
            'diagnostic_route_exists' => $routes->getByName('') ? 'YES' : 'Checking...',
        ];
    } catch (\Throwable $e) {
        $report['routes']['error'] = $e->getMessage();
    }
    
    // 4. Database Connection
    try {
        $db = $app['db'];
        $connected = $db->connection()->getPdo() !== null;
        $report['database'] = [
            'connected' => $connected ? 'YES' : 'NO',
            'driver' => $app['config']['database.default'],
            'host' => $app['config']['database.connections.mysql.host'],
            'database' => $app['config']['database.connections.mysql.database'],
        ];
    } catch (\Throwable $e) {
        $report['database']['error'] = $e->getMessage();
    }
    
    // 5. Cache Configuration
    $report['cache'] = [
        'driver' => $app['config']['cache.default'],
        'is_enabled' => $app['config']['cache.default'] !== 'null' ? 'YES' : 'NO',
    ];
    
    // 6. Session Configuration
    $report['session'] = [
        'driver' => $app['config']['session.driver'],
        'cookie_name' => $app['config']['session.cookie'],
    ];
    
    // 7. Auth Configuration
    $report['auth'] = [
        'default_guard' => $app['config']['auth.defaults.guard'],
        'user_model' => $app['config']['auth.providers.users.model'],
    ];
    
    // 8. Key Services
    $services = [
        'ComplianceOrchestrator' => "$baseDir/app/Services/Compliance/ComplianceOrchestrator.php",
        'ComplianceTestAnalyzer' => "$baseDir/app/Services/Compliance/Testing/ComplianceTestAnalyzer.php",
        'FormApiServiceFactory' => "$baseDir/app/Services/Compliance/FormApis/FormApiServiceFactory.php",
    ];
    
    $report['services'] = [];
    foreach ($services as $name => $path) {
        $report['services'][$name] = file_exists($path) ? 'EXISTS' : 'MISSING';
    }
    
    // 9. Controllers
    $controllers = [
        'AuthController' => "$baseDir/app/Http/Controllers/AuthController.php",
        'ComplianceExecutionController' => "$baseDir/app/Http/Controllers/ComplianceExecutionController.php",
    ];
    
    $report['controllers'] = [];
    foreach ($controllers as $name => $path) {
        $report['controllers'][$name] = file_exists($path) ? 'EXISTS' : 'MISSING';
    }
    
    // 10. Sample Routes
    try {
        $router = $app['router'];
        $routes = $router->getRoutes();
        $report['sample_routes'] = [];
        
        $count = 0;
        foreach ($routes as $route) {
            if ($count >= 25) break;
            $report['sample_routes'][] = [
                'method' => implode('|', $route->methods),
                'uri' => $route->uri,
                'name' => $route->getName() ?? 'unnamed',
            ];
            $count++;
        }
    } catch (\Throwable $e) {
        $report['sample_routes'] = ['error' => $e->getMessage()];
    }
    
    // 11. Logs Sample
    $logFile = "$baseDir/storage/logs/laravel.log";
    if (file_exists($logFile)) {
        $lines = array_slice(file($logFile), -30);
        $report['recent_logs'] = array_map('trim', $lines);
    } else {
        $report['recent_logs'] = ['status' => 'Log file not found'];
    }
    
    // 12. Deployment Info
    $report['deployment'] = [
        'base_path' => $baseDir,
        'public_path' => __DIR__,
        'app_url_config' => $app['config']['app.url'],
        'subdirectory_deployed' => str_contains($app['config']['app.url'], '/compliance/ce'),
    ];
    
    // Output JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (\Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
    ], JSON_PRETTY_PRINT);
}
