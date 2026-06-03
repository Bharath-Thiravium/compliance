<?php
/**
 * Simple Health Check
 * Usage: https://athenas.co.in/compliance/ce/public/health.php
 */

$baseDir = dirname(__DIR__);
$output = [];

// Check file system
$output['files'] = [
    'index.php' => file_exists("$baseDir/public/index.php"),
    'bootstrap/app.php' => file_exists("$baseDir/bootstrap/app.php"),
    'routes/web.php' => file_exists("$baseDir/routes/web.php"),
];

// Try simple bootstrap
try {
    require_once "$baseDir/vendor/autoload.php";
    $output['autoload'] = 'OK';
    
    $app = require_once "$baseDir/bootstrap/app.php";
    $output['bootstrap'] = 'OK';
    
    $output['app_url'] = config('app.url');
    
} catch (\Throwable $e) {
    $output['error'] = $e->getMessage();
    $output['file'] = $e->getFile();
    $output['line'] = $e->getLine();
}

header('Content-Type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT);
