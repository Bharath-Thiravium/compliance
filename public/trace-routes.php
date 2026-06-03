<?php
/**
 * Advanced Route Diagnostic - Traces routing process
 * Usage: https://athenas.co.in/compliance/ce/public/trace-routes.php
 */

$baseDir = dirname(__DIR__);
$report = [];

try {
    $report['step1_autoload'] = 'Loading...';
    require_once "$baseDir/vendor/autoload.php";
    $report['step1_autoload'] = '✓ Autoload loaded';
    
    $report['step2_bootstrap'] = 'Loading...';
    $app = require_once "$baseDir/bootstrap/app.php";
    $report['step2_bootstrap'] = '✓ App bootstrapped';
    
    $report['step3_router'] = 'Getting router...';
    $router = $app['router'];
    $report['step3_router'] = '✓ Router instance obtained';
    
    $report['step4_before_loading'] = 'Getting routes before loading...';
    $routesBefore = $router->getRoutes();
    $report['step4_before_loading'] = count($routesBefore) . ' routes (before load)';
    
    $report['step5_load_web'] = 'Loading routes/web.php...';
    require "$baseDir/routes/web.php";
    $report['step5_load_web'] = '✓ web.php loaded';
    
    $report['step6_after_loading'] = 'Getting routes after loading...';
    $routesAfter = $router->getRoutes();
    $report['step6_after_loading'] = count($routesAfter) . ' routes (after load)';
    
    $report['step7_check_login'] = 'Checking login route...';
    $loginRoute = $routesAfter->getByName('login');
    $report['step7_check_login'] = $loginRoute ? '✓ Login route found' : '✗ Login route NOT found';
    
    // Get all route names
    $report['all_routes'] = [];
    foreach ($routesAfter as $route) {
        $report['all_routes'][] = [
            'uri' => $route->uri,
            'name' => $route->getName() ?? '(unnamed)',
            'methods' => implode(',', $route->methods),
        ];
    }
    
    $report['summary'] = [
        'total_routes' => count($routesAfter),
        'routes_loaded' => count($routesAfter) > 0 ? '✓ YES' : '✗ NO - ZERO ROUTES!',
    ];
    
} catch (\Throwable $e) {
    $report['error'] = $e->getMessage();
    $report['file'] = $e->getFile();
    $report['line'] = $e->getLine();
    $report['trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 15);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
