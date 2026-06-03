<?php
/**
 * Minimal Diagnostic Tool - No Laravel Config Dependency
 * Usage: https://athenas.co.in/compliance/ce/public/simple-diagnostic.php
 */

$baseDir = dirname(__DIR__);
$report = [];

// 1. Basic PHP Info
$report['php'] = [
    'version' => PHP_VERSION,
    'sapi' => php_sapi_name(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
];

// 2. File System Check
$report['files'] = [
    'vendor_autoload' => file_exists("$baseDir/vendor/autoload.php") ? '✓' : '✗',
    'bootstrap_app' => file_exists("$baseDir/bootstrap/app.php") ? '✓' : '✗',
    'routes_web' => file_exists("$baseDir/routes/web.php") ? '✓' : '✗',
    'routes_compliance' => file_exists("$baseDir/routes/compliance.php") ? '✓' : '✗',
    'routes_diagnostics' => file_exists("$baseDir/routes/diagnostics.php") ? '✓' : '✗',
    'env_file' => file_exists("$baseDir/.env") ? '✓' : '✗',
];

// 3. Parse .env file
if (file_exists("$baseDir/.env")) {
    $env = parse_ini_file("$baseDir/.env");
    $report['env'] = [
        'APP_ENV' => $env['APP_ENV'] ?? 'NOT_SET',
        'APP_DEBUG' => $env['APP_DEBUG'] ?? 'NOT_SET',
        'APP_URL' => $env['APP_URL'] ?? 'NOT_SET',
        'DB_CONNECTION' => $env['DB_CONNECTION'] ?? 'NOT_SET',
        'DB_HOST' => $env['DB_HOST'] ?? 'NOT_SET',
        'DB_DATABASE' => $env['DB_DATABASE'] ?? 'NOT_SET',
    ];
}

// 4. Try to bootstrap Laravel
$report['bootstrap'] = [];
try {
    require_once "$baseDir/vendor/autoload.php";
    $report['bootstrap']['autoload'] = '✓ Loaded';
} catch (\Throwable $e) {
    $report['bootstrap']['autoload'] = '✗ Error: ' . $e->getMessage();
    $report['error'] = $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $app = require_once "$baseDir/bootstrap/app.php";
    $report['bootstrap']['app_bootstrap'] = '✓ Loaded';
} catch (\Throwable $e) {
    $report['bootstrap']['app_bootstrap'] = '✗ Error: ' . $e->getMessage();
    $report['error'] = $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 5. Try to get routes without accessing config
$report['routes'] = [];
try {
    $router = $app['router'];
    $allRoutes = $router->getRoutes();
    $report['routes']['total'] = count($allRoutes);
    $report['routes']['get_routes'] = count(array_filter($allRoutes, fn($r) => in_array('GET', $r->methods)));
    $report['routes']['post_routes'] = count(array_filter($allRoutes, fn($r) => in_array('POST', $r->methods)));
    
    // Check for key routes
    $report['routes']['login_exists'] = null !== $allRoutes->getByName('login') ? '✓' : '✗';
    
    // Sample first 30 routes
    $report['routes']['sample'] = [];
    foreach (array_slice($allRoutes, 0, 30) as $route) {
        $report['routes']['sample'][] = [
            'method' => implode(',', $route->methods),
            'uri' => $route->uri,
            'name' => $route->getName() ?? '(unnamed)',
        ];
    }
} catch (\Throwable $e) {
    $report['routes']['error'] = $e->getMessage();
}

// 6. Check database connection
$report['database'] = [];
try {
    $db = $app['db'];
    $pdo = $db->connection()->getPdo();
    $report['database']['connected'] = $pdo !== null ? '✓' : '✗';
    
    // Get database name from .env
    $dbName = $env['DB_DATABASE'] ?? 'unknown';
    $report['database']['database'] = $dbName;
    
    // Count tables
    if ($pdo !== null) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$dbName'");
        $count = $stmt->fetchColumn();
        $report['database']['table_count'] = $count;
    }
} catch (\Throwable $e) {
    $report['database']['error'] = $e->getMessage();
}

// 7. Service Providers
$report['providers'] = [];
try {
    $providers = $app->getLoadedProviders();
    $report['providers']['count'] = count($providers);
    $report['providers']['list'] = array_keys($providers);
} catch (\Throwable $e) {
    $report['providers']['error'] = $e->getMessage();
}

// 8. Check Critical Services
$report['services'] = [
    'ComplianceOrchestrator' => file_exists("$baseDir/app/Services/Compliance/ComplianceOrchestrator.php") ? '✓' : '✗',
    'ComplianceTestAnalyzer' => file_exists("$baseDir/app/Services/Compliance/Testing/ComplianceTestAnalyzer.php") ? '✓' : '✗',
    'FormApiServiceFactory' => file_exists("$baseDir/app/Services/Compliance/FormApis/FormApiServiceFactory.php") ? '✓' : '✗',
];

// 9. Check Critical Controllers
$report['controllers'] = [
    'AuthController' => file_exists("$baseDir/app/Http/Controllers/AuthController.php") ? '✓' : '✗',
    'ComplianceExecutionController' => file_exists("$baseDir/app/Http/Controllers/ComplianceExecutionController.php") ? '✓' : '✗',
];

// 10. Recent Logs
$report['logs'] = [];
$logFile = "$baseDir/storage/logs/laravel.log";
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -50);
    $report['logs'] = array_map('trim', array_filter($lines));
} else {
    $report['logs']['status'] = 'Log file not found';
}

// 11. Summary
$report['summary'] = [
    'status' => 'OK - Application is running',
    'routes_loaded' => $report['routes']['total'] ?? 0,
    'database_connected' => isset($report['database']['connected']) && $report['database']['connected'] === '✓' ? 'YES' : 'NO',
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
