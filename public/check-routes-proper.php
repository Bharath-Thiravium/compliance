<?php
/**
 * Proper Route Diagnostic - Uses HTTP Kernel
 * Usage: https://athenas.co.in/compliance/ce/public/check-routes-proper.php
 */

$baseDir = dirname(__DIR__);
$report = [];

try {
    $report['step1'] = 'Loading autoload...';
    require_once "$baseDir/vendor/autoload.php";
    $report['step1'] = '✓ Autoload loaded';
    
    $report['step2'] = 'Bootstrapping app...';
    $app = require_once "$baseDir/bootstrap/app.php";
    $report['step2'] = '✓ App bootstrapped';
    
    $report['step3'] = 'Creating HTTP kernel...';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $report['step3'] = '✓ HTTP kernel created';
    
    $report['step4'] = 'Creating request...';
    $request = \Illuminate\Http\Request::create('/login', 'GET');
    $report['step4'] = '✓ Request created';
    
    $report['step5'] = 'Getting registered routes...';
    $router = $app['router'];
    
    // Try to get routes - at this point they should be registered
    $allRoutes = $router->getRoutes();
    $report['step5'] = '✓ Routes retrieved: ' . count($allRoutes) . ' total';
    
    // Check specific routes
    $report['login_route'] = $allRoutes->getByName('login') ? '✓ Found' : '✗ NOT found';
    $report['compliance_routes_count'] = count(array_filter($allRoutes, fn($r) => str_contains($r->uri, 'compliance')));
    
    // Sample routes
    $report['sample_routes'] = [];
    foreach (array_slice($allRoutes, 0, 20) as $route) {
        $report['sample_routes'][] = [
            'uri' => $route->uri,
            'name' => $route->getName() ?? '(unnamed)',
            'methods' => implode(',', $route->methods),
        ];
    }
    
    $report['summary'] = [
        'total_routes' => count($allRoutes),
        'status' => count($allRoutes) > 0 ? '✓ Routes are loading!' : '✗ STILL 0 ROUTES',
    ];
    
} catch (\Throwable $e) {
    $report['error'] = $e->getMessage();
    $report['exception_class'] = get_class($e);
    $report['file'] = $e->getFile() . ':' . $e->getLine();
    $report['trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 10);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
