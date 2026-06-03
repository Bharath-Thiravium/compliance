<?php
// ROUTE CACHE CHECKER & CLEARER
// Place at: /public_html/compliance/ce/public/check-routes.php

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>Route Cache Check</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo ".ok{color:#4ade80;} .err{color:#f87171;} .warn{color:#fbbf24;} pre{background:#0f172a;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>Route Cache Diagnostic</h1>";

echo "<h2>1. Check for Route Cache Files</h2>";

$cacheDir = "$appRoot/bootstrap/cache";
$routeCacheFiles = [
    'routes-v7.php',
    'routes.php',
];

foreach ($routeCacheFiles as $file) {
    $path = "$cacheDir/$file";
    if (file_exists($path)) {
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "<span class='warn'>⚠️</span> CACHE EXISTS: $file ($size bytes, modified: $modified)<br>";
        echo "<strong>This might be stale!</strong><br><br>";
    }
}

echo "<h2>2. Check if routes/web.php exists</h2>";
$webRoutesPath = "$appRoot/routes/web.php";
if (file_exists($webRoutesPath)) {
    $size = filesize($webRoutesPath);
    echo "<span class='ok'>✓</span> routes/web.php exists ($size bytes)<br>";
} else {
    echo "<span class='err'>✗</span> routes/web.php NOT FOUND<br>";
}

echo "<h2>3. Load Routes Directly</h2>";
try {
    require "$appRoot/vendor/autoload.php";
    $app = require_once "$appRoot/bootstrap/app.php";
    
    $router = $app['router'];
    $routes = $router->getRoutes();
    
    $routeCount = count($routes);
    echo "<span class='ok'>✓</span> Loaded $routeCount routes<br>";
    
    if ($routeCount > 0) {
        echo "<br><strong>First 10 routes:</strong><br>";
        $i = 0;
        foreach ($routes as $route) {
            if ($i++ >= 10) break;
            echo "- " . $route->getPath() . " (" . implode(',', $route->methods()) . ")<br>";
        }
    } else {
        echo "<span class='err'>✗ NO ROUTES LOADED!</span><br>";
        echo "This means routes/web.php is not being loaded properly<br>";
    }
    
    // Check for login route specifically
    $loginFound = false;
    foreach ($routes as $route) {
        if ($route->getName() === 'login') {
            $loginFound = true;
            break;
        }
    }
    
    if ($loginFound) {
        echo "<br><span class='ok'>✓ Login route EXISTS</span><br>";
    } else {
        echo "<br><span class='err'>✗ Login route NOT FOUND</span><br>";
    }
    
} catch (Throwable $e) {
    echo "<span class='err'>✗ Error loading app: " . $e->getMessage() . "</span><br>";
}

echo "<h2>4. Manual Clear Instructions</h2>";
echo "<p>If routes are not loading, run on server via SSH:</p>";
echo "<pre>";
echo "cd $appRoot\n";
echo "php artisan route:clear\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan route:cache\n";
echo "</pre>";

echo "<p>Or delete cache files manually:</p>";
echo "<pre>";
echo "rm $cacheDir/routes*.php\n";
echo "rm $cacheDir/config.php\n";
echo "</pre>";

echo "</body></html>";
?>
