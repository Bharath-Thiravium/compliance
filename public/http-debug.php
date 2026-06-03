<?php
/**
 * HTTP Request Flow Debug
 * Usage: https://athenas.co.in/compliance/ce/public/http-debug.php
 */

$baseDir = dirname(__DIR__);
$output = [];

$output['step_1'] = 'Starting diagnostic...';

try {
    // Step 1: Autoload
    $output['step_2_autoload'] = 'Loading...';
    require_once "$baseDir/vendor/autoload.php";
    $output['step_2_autoload'] = '✓ OK';
    
    // Step 2: Bootstrap
    $output['step_3_bootstrap'] = 'Bootstrapping...';
    $app = require_once "$baseDir/bootstrap/app.php";
    $output['step_3_bootstrap'] = '✓ OK';
    
    // Step 3: Create HTTP Kernel
    $output['step_4_kernel'] = 'Creating HTTP kernel...';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $output['step_4_kernel'] = '✓ OK';
    
    // Step 4: Create Request
    $output['step_5_request'] = 'Creating request...';
    $request = \Illuminate\Http\Request::capture();
    $output['step_5_request'] = '✓ OK';
    $output['request_details'] = [
        'method' => $request->method(),
        'path' => $request->path(),
        'url' => $request->url(),
        'request_uri' => $request->server('REQUEST_URI'),
    ];
    
    // Step 5: Handle request through HTTP kernel
    $output['step_6_handle'] = 'Handling request through HTTP kernel...';
    $response = $kernel->handle($request);
    $output['step_6_handle'] = '✓ OK';
    $output['response_status'] = $response->status();
    $output['response_headers'] = $response->headers->all();
    
    // Step 6: Get routes
    $output['step_7_routes'] = 'Getting routes...';
    $router = $app['router'];
    $allRoutes = $router->getRoutes();
    $output['step_7_routes'] = '✓ OK - ' . count($allRoutes) . ' routes';
    
    // Check for key routes
    $output['key_routes'] = [
        'login' => $allRoutes->getByName('login') ? 'EXISTS' : 'MISSING',
        'compliance.dashboard' => $allRoutes->getByName('compliance.dashboard') ? 'EXISTS' : 'MISSING',
        'super-admin.dashboard' => $allRoutes->getByName('super-admin.dashboard') ? 'EXISTS' : 'MISSING',
    ];
    
    // Test config access
    $output['step_8_config'] = 'Testing config access...';
    try {
        $appUrl = config('app.url');
        $output['step_8_config'] = '✓ OK - APP_URL: ' . $appUrl;
    } catch (\Throwable $e) {
        $output['step_8_config'] = '✗ Error: ' . $e->getMessage();
    }
    
    $output['status'] = 'DIAGNOSTIC COMPLETE';
    
} catch (\Throwable $e) {
    $output['error'] = $e->getMessage();
    $output['exception_class'] = get_class($e);
    $output['file'] = $e->getFile() . ':' . $e->getLine();
    $output['trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 15);
    $output['status'] = 'ERROR';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
