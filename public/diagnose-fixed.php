<?php
// CORRECTED DIAGNOSTIC
// Place at: /public_html/compliance/ce/public/diagnose-fixed.php

header('Content-Type: text/plain');

echo "=== CORRECTED HOSTINGER DIAGNOSTIC ===\n\n";

echo "ACTUAL LOCATIONS:\n";
echo "Script location (__FILE__): " . __FILE__ . "\n";
echo "Script dir (__DIR__): " . __DIR__ . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";

// Calculate base directory correctly
// __FILE__ = /home/.../public_html/compliance/ce/public/diagnose-fixed.php
// __DIR__  = /home/.../public_html/compliance/ce/public
// dirname(__DIR__) = /home/.../public_html/compliance/ce ← THIS IS CORRECT
// dirname(dirname(__DIR__)) = /home/.../public_html/compliance ← WRONG

$publicDir = __DIR__;  // /compliance/ce/public
$appRoot = dirname($publicDir);  // /compliance/ce

echo "\n--- CORRECTED PATH ANALYSIS ---\n";
echo "Public directory: $publicDir\n";
echo "App root: $appRoot\n";

echo "\n--- FILES THAT SHOULD EXIST ---\n";
$requiredFiles = [
    '.env' => "$appRoot/.env",
    'vendor/autoload.php' => "$appRoot/vendor/autoload.php",
    'bootstrap/app.php' => "$appRoot/bootstrap/app.php",
    'routes/web.php' => "$appRoot/routes/web.php",
    'artisan' => "$appRoot/artisan",
];

foreach ($requiredFiles as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? "✓ EXISTS" : "✗ MISSING";
    echo "$status: $name\n";
    if (!$exists) {
        echo "  Expected at: $path\n";
    }
}

echo "\n--- DIRECTORY CHECK ---\n";
$dirs = [
    'app' => "$appRoot/app",
    'bootstrap' => "$appRoot/bootstrap",
    'config' => "$appRoot/config",
    'public' => "$appRoot/public",
    'routes' => "$appRoot/routes",
    'storage' => "$appRoot/storage",
    'vendor' => "$appRoot/vendor",
];

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    $status = $exists ? "✓ EXISTS" : "✗ MISSING";
    echo "$status: $name/\n";
}

echo "\n--- BOOTSTRAP TEST ---\n";
try {
    $autoloadPath = "$appRoot/vendor/autoload.php";
    if (!file_exists($autoloadPath)) {
        throw new Exception("Autoloader not found at: $autoloadPath");
    }
    
    echo "Loading autoloader from: $autoloadPath\n";
    require $autoloadPath;
    echo "✓ Autoloader loaded\n";
    
    $appPath = "$appRoot/bootstrap/app.php";
    if (!file_exists($appPath)) {
        throw new Exception("Bootstrap app not found at: $appPath");
    }
    
    echo "Loading app from: $appPath\n";
    $app = require_once $appPath;
    echo "✓ App bootstrapped\n";
    
    echo "\nConfiguration:\n";
    echo "  APP_ENV: " . config('app.env') . "\n";
    echo "  APP_URL: " . config('app.url') . "\n";
    echo "  APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
    
    echo "\n✓ SUCCESS - Laravel is working!\n";
    
} catch (Throwable $e) {
    echo "✗ FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n--- ROOT DIAGNOSIS ---\n";
$allFilesExist = true;
foreach ($requiredFiles as $name => $path) {
    if (!file_exists($path)) {
        $allFilesExist = false;
        break;
    }
}

if (!$allFilesExist) {
    echo "✗ FILES MISSING - Application not fully deployed\n";
    echo "Action: Upload entire project to /public_html/compliance/ce/\n";
    echo "Then run: composer install --no-dev --optimize-autoloader\n";
} else {
    echo "✓ All files exist - Check errors above\n";
}
?>
