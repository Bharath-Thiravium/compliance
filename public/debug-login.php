<?php
/**
 * Login Redirect Debug
 * Usage: https://athenas.co.in/compliance/ce/public/debug-login.php?token=ops_compliance_2026
 */

$validToken = 'ops_compliance_2026';
$providedToken = $_GET['token'] ?? '';

if ($providedToken !== $validToken) {
    http_response_code(403);
    die('{"error": "Unauthorized"}');
}

$baseDir = dirname(__DIR__);
$output = [];

try {
    require_once "$baseDir/vendor/autoload.php";
    $app = require_once "$baseDir/bootstrap/app.php";
    
    $output['bootstrap'] = '✓ OK';
    
    // Test route generation
    $output['routes'] = [];
    
    // Get router
    $router = $app['router'];
    $routes = $router->getRoutes();
    
    $output['routes']['total'] = count($routes);
    
    // Check dashboard route
    $dashboardRoute = $routes->getByName('compliance.dashboard');
    if ($dashboardRoute) {
        $output['routes']['dashboard_uri'] = $dashboardRoute->uri;
        $output['routes']['dashboard_methods'] = implode(',', $dashboardRoute->methods);
    }
    
    // Check super admin route
    $superAdminRoute = $routes->getByName('super-admin.dashboard');
    if ($superAdminRoute) {
        $output['routes']['super_admin_uri'] = $superAdminRoute->uri;
        $output['routes']['super_admin_methods'] = implode(',', $superAdminRoute->methods);
    }
    
    // Check login route
    $loginRoute = $routes->getByName('login');
    if ($loginRoute) {
        $output['routes']['login_uri'] = $loginRoute->uri;
        $output['routes']['login_methods'] = implode(',', $loginRoute->methods);
    }
    
    // Check config
    $output['config'] = [
        'app_url' => config('app.url'),
        'session_path' => config('session.path'),
        'session_domain' => config('session.domain'),
        'session_secure' => config('session.secure'),
        'session_http_only' => config('session.http_only'),
        'session_same_site' => config('session.same_site'),
    ];
    
    // Check if redirect middleware exists
    $output['middleware'] = [];
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $output['middleware']['route_middleware'] = array_keys($kernel->routeMiddleware);
    
    // Check auth config
    $output['auth'] = [
        'default_guard' => config('auth.defaults.guard'),
        'guard_driver' => config('auth.guards.web.driver'),
        'guard_provider' => config('auth.guards.web.provider'),
    ];
    
    // Check if there's a LoginResponse class or redirect logic
    $output['files_check'] = [
        'AuthController' => file_exists($baseDir . '/app/Http/Controllers/AuthController.php'),
        'Http/Responses/LoginResponse' => file_exists($baseDir . '/app/Http/Responses/LoginResponse.php'),
        'Http/Middleware/Authenticate' => file_exists($baseDir . '/app/Http/Middleware/Authenticate.php'),
    ];
    
    // Try to manually generate the redirect URL
    $output['redirect_test'] = [
        'app_url' => config('app.url'),
        'dashboard_full_url' => config('app.url') . '/compliance/dashboard',
        'super_admin_full_url' => config('app.url') . '/super-admin/dashboard',
    ];
    
} catch (\Throwable $e) {
    $output['error'] = $e->getMessage();
    $output['file'] = $e->getFile();
    $output['line'] = $e->getLine();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
