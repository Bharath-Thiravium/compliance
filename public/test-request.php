<?php
// PROPER LARAVEL REQUEST TEST
// Place at: /public_html/compliance/ce/public/test-request.php

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>Request Test</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo ".ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}</style>";
echo "</head><body>";

echo "<h1>Proper Laravel Request Test</h1>";

try {
    // Step 1: Load Laravel properly
    require "$appRoot/vendor/autoload.php";
    $app = require_once "$appRoot/bootstrap/app.php";
    
    echo "<span class='ok'>✓</span> Laravel app instantiated<br>";
    
    // Step 2: Create a real HTTP request
    $method = 'GET';
    $uri = '/login';
    $request = \Illuminate\Http\Request::create($uri, $method);
    
    echo "<span class='ok'>✓</span> Created request for: $uri<br>";
    
    // Step 3: Bind request to app
    $app->instance('request', $request);
    
    echo "<span class='ok'>✓</span> Bound request to app<br>";
    
    // Step 4: Get the HTTP kernel
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "<span class='ok'>✓</span> Got HTTP kernel<br>";
    
    // Step 5: Try to handle the request
    try {
        $response = $kernel->handle($request);
        echo "<span class='ok'>✓</span> Request handled successfully<br>";
        echo "<p>Response status: " . $response->getStatusCode() . "</p>";
        
        if ($response->getStatusCode() === 200) {
            echo "<span class='ok'>✓ LOGIN PAGE WORKS!</span><br>";
            echo "<p><a href='../../login'>Try /login now</a></p>";
        } else {
            echo "<p>Status: " . $response->getStatusCode() . "</p>";
        }
    } catch (\Throwable $e) {
        echo "<span class='err'>✗</span> Request handling error: " . $e->getMessage() . "<br>";
        echo "This might be the routing issue<br>";
    }
    
} catch (\Throwable $e) {
    echo "<span class='err'>✗</span> Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
