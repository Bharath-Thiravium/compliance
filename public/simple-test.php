<?php
// ULTRA-SIMPLE TEST - No path confusion
// Place at: /public_html/compliance/ce/public/simple-test.php

// We know we're at: /public_html/compliance/ce/public/simple-test.php
// So parent is: /public_html/compliance/ce

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>Simple Test</title>";
echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green;font-weight:bold;} .err{color:red;font-weight:bold;}</style>";
echo "</head><body>";

echo "<h1>Direct Path Test</h1>";
echo "<p>App Root: $appRoot</p>";

echo "<h2>Step 1: Check Files</h2>";
$files = ['.env', 'vendor/autoload.php', 'bootstrap/app.php'];
foreach ($files as $file) {
    $path = "$appRoot/$file";
    $exists = file_exists($path);
    $status = $exists ? '<span class="ok">✓</span>' : '<span class="err">✗</span>';
    echo "$status $file: ";
    echo $exists ? "EXISTS" : "NOT FOUND at $path";
    echo "<br>";
}

echo "<h2>Step 2: Bootstrap Laravel</h2>";
try {
    require "$appRoot/vendor/autoload.php";
    echo "<span class='ok'>✓</span> Autoloader loaded<br>";
    
    $app = require_once "$appRoot/bootstrap/app.php";
    echo "<span class='ok'>✓</span> App bootstrapped<br>";
    
    echo "<h2>Step 3: Get Configuration</h2>";
    $env = $app['config']->get('app.env');
    $url = $app['config']->get('app.url');
    echo "<span class='ok'>✓</span> APP_ENV: $env<br>";
    echo "<span class='ok'>✓</span> APP_URL: $url<br>";
    
    echo "<h2>Step 4: Test Routing</h2>";
    $router = $app['router'];
    $routes = $router->getRoutes();
    
    $loginFound = false;
    foreach ($routes as $route) {
        if ($route->getName() === 'login') {
            $loginFound = true;
            break;
        }
    }
    
    if ($loginFound) {
        echo "<span class='ok'>✓</span> Login route found!<br>";
    } else {
        echo "<span class='err'>✗</span> Login route NOT found<br>";
        echo "Total routes: " . count($routes) . "<br>";
    }
    
    echo "<h2 style='color:green;'>✓ SUCCESS - Everything is working!</h2>";
    echo "<p>Try: <a href='../../login'>https://athenas.co.in/compliance/ce/login</a></p>";
    
} catch (Throwable $e) {
    echo "<span class='err'>✗</span> Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
