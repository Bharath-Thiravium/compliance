<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// CRITICAL FIX FOR SUBDIRECTORY DEPLOYMENT
// Strip /compliance/ce from the request path so Laravel sees /login instead of /compliance/ce/login
$request = Request::capture();

// Get the current REQUEST_URI
$requestUri = $request->getRequestUri();
$subdirectory = '/compliance/ce';

// If the request URI starts with the subdirectory, remove it
if (strpos($requestUri, $subdirectory) === 0) {
    $newUri = substr($requestUri, strlen($subdirectory));
    if (empty($newUri)) {
        $newUri = '/';
    }
    
    // Recreate the request with the corrected path
    $_SERVER['REQUEST_URI'] = $newUri;
    $_SERVER['PATH_INFO'] = $newUri;
    $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    $_SERVER['PHP_SELF'] = '/public/index.php';
    
    // Recapture request with fixed values
    $request = Request::capture();
}

$app->handleRequest($request);
