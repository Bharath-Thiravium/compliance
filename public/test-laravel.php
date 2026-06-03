<?php
// DIRECT LARAVEL TEST - Place in e:\CEngine\public\test-laravel.php
// Visit: https://athenas.co.in/compliance/ce/public/test-laravel.php

$start = microtime(true);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Laravel Direct Test</title>";
echo "<style>body{font-family:monospace;background:#1e293b;color:#e2e8f0;padding:20px;}";
echo ".pass{color:#4ade80;}.fail{color:#f87171;}.info{color:#60a5fa;}</style></head><body>";

echo "<h1>Laravel Bootstrap Test</h1>";

// Step 1: Check file paths
echo "<h2>Step 1: File Structure</h2>";
$base = dirname(dirname(__FILE__));
$checks = [
    '.env' => "$base/.env",
    'vendor/autoload.php' => "$base/vendor/autoload.php",
    'bootstrap/app.php' => "$base/bootstrap/app.php",
    'routes/web.php' => "$base/routes/web.php",
    'app directory' => "$base/app",
];

foreach ($checks as $name => $path) {
    $exists = (strpos($path, '/') === strrpos($path, '/')) 
        ? file_exists($path)
        : is_dir($path);
    $status = $exists ? '<span class="pass">✓</span>' : '<span class="fail">✗</span>';
    echo "$status $name: $path<br>";
}

// Step 2: Try to bootstrap Laravel
echo "<h2>Step 2: Bootstrap Laravel</h2>";
try {
    echo "Loading autoloader...";
    require "$base/vendor/autoload.php";
    echo " <span class='pass'>✓</span><br>";
    
    echo "Loading app...";
    $app = require_once "$base/bootstrap/app.php";
    echo " <span class='pass'>✓</span><br>";
    
    echo "Getting config...";
    $config = $app->make('config');
    echo " <span class='pass'>✓</span><br>";
    
    echo "<h2>Configuration</h2>";
    echo "<div class='info'>";
    echo "APP_ENV: " . config('app.env') . "<br>";
    echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "<br>";
    echo "APP_URL: " . config('app.url') . "<br>";
    echo "</div>";
    
} catch (Throwable $e) {
    echo " <span class='fail'>✗</span><br>";
    echo "<div class='fail'>";
    echo "<h3>Error:</h3>";
    echo $e->getMessage() . "<br>";
    echo $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "</div>";
}

// Step 3: Test route matching
echo "<h2>Step 3: Route Test</h2>";
try {
    $router = $app->make('router');
    
    echo "Testing route matching for '/login':<br>";
    
    // Get all routes
    $routes = $router->getRoutes();
    $found = false;
    
    foreach ($routes as $route) {
        if ($route->getName() === 'login') {
            $found = true;
            echo "<span class='pass'>✓ Found route: /login</span><br>";
            echo "Methods: " . implode(', ', $route->methods()) . "<br>";
            break;
        }
    }
    
    if (!$found) {
        echo "<span class='fail'>✗ Route '/login' not found</span><br>";
        echo "Available routes:<br>";
        foreach ($routes as $route) {
            echo "- " . $route->getPath() . " (" . implode(',', $route->methods()) . ")<br>";
        }
    }
    
} catch (Throwable $e) {
    echo "<span class='fail'>✗ Error testing routes: " . $e->getMessage() . "</span><br>";
}

$elapsed = (microtime(true) - $start) * 1000;
echo "<hr>";
echo "<small>Execution time: {$elapsed}ms</small>";
echo "</body></html>";
?>
