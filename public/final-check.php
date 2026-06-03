<?php
// FINAL ROUTE DIAGNOSTIC
// Place at: /public_html/compliance/ce/public/final-check.php

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>Final Route Check</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo ".ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;} pre{background:#0f172a;padding:10px;}</style>";
echo "</head><body>";

echo "<h1>Final Route Diagnostic After Composer Fix</h1>";

try {
    require "$appRoot/vendor/autoload.php";
    echo "<span class='ok'>✓</span> Autoloader loaded<br>";
    
    $app = require_once "$appRoot/bootstrap/app.php";
    echo "<span class='ok'>✓</span> App bootstrapped<br>";
    
    $router = $app['router'];
    echo "<span class='ok'>✓</span> Router accessed<br>";
    
    $routes = $router->getRoutes();
    $count = count($routes);
    
    if ($count > 0) {
        echo "<span class='ok'>✓ SUCCESS: Loaded " . $count . " routes!</span><br>";
        echo "<h2>First 15 routes:</h2>";
        echo "<pre>";
        $i = 0;
        foreach ($routes as $route) {
            if ($i++ >= 15) break;
            echo $route->getPath() . " (" . implode(',', $route->methods()) . ")\n";
        }
        echo "</pre>";
        
        // Check for login specifically
        $found = false;
        foreach ($routes as $route) {
            if ($route->getName() === 'login') {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "<span class='ok'>✓ Login route FOUND</span><br>";
            echo "<p>Try: <a href='../../login'>/login</a></p>";
        } else {
            echo "<span class='err'>✗ Login route NOT found</span><br>";
        }
    } else {
        echo "<span class='err'>✗ Still 0 routes loaded!</span><br>";
        echo "<p>This means routes/web.php is still not being parsed.</p>";
        echo "<p>Possible causes:</p>";
        echo "<ul>";
        echo "<li>PHP syntax error in route files</li>";
        echo "<li>Missing controller classes</li>";
        echo "<li>Service provider not registering routes</li>";
        echo "</ul>";
    }
    
} catch (Throwable $e) {
    echo "<span class='err'>✗ Error: " . $e->getMessage() . "</span><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
