<?php
// DIRECT LARAVEL REQUEST HANDLER
// Place at: /public_html/compliance/ce/public/direct-login.php
// Visit: https://athenas.co.in/compliance/ce/public/direct-login.php

// Calculate correct app root
$appRoot = dirname(__DIR__);

// Load Laravel
try {
    require "$appRoot/vendor/autoload.php";
    $app = require_once "$appRoot/bootstrap/app.php";
    
    // Create a fake request for /login
    $request = \Illuminate\Http\Request::create('/login', 'GET');
    
    // Set up the request context
    $app['request'] = $request;
    
    // Get the router
    $router = $app['router'];
    
    // Try to match the route
    try {
        $route = $router->getRoutes()->match($request);
        
        echo "<!DOCTYPE html><html><head><title>✓ Route Matching Success</title></head><body>";
        echo "<h1>✓ Route Matching Works!</h1>";
        echo "<p>Route matched: " . $route->getUri() . "</p>";
        echo "<p>Controller: " . (method_exists($route, 'getControllerName') ? $route->getControllerName() : 'N/A') . "</p>";
        echo "<p>This means /login route exists and can be matched.</p>";
        echo "<p>The issue is somewhere else in the request handling.</p>";
        echo "</body></html>";
        
    } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
        echo "<!DOCTYPE html><html><head><title>✗ Route Not Found</title></head><body>";
        echo "<h1>✗ Route /login Not Found</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<h2>Available Routes:</h2>";
        echo "<ul>";
        foreach ($router->getRoutes() as $route) {
            if (strpos($route->getUri(), 'login') !== false) {
                echo "<li>" . $route->getUri() . " (" . implode(',', $route->methods()) . ")</li>";
            }
        }
        echo "</ul>";
        echo "</body></html>";
    }
    
} catch (Throwable $e) {
    echo "<!DOCTYPE html><html><head><title>✗ Laravel Bootstrap Failed</title></head><body>";
    echo "<h1>✗ Laravel Bootstrap Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</body></html>";
}
?>
