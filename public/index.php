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
// Strip /compliance/ce/public from the request path so Laravel sees /login instead of /compliance/ce/public/http-debug.php
$request = Request::capture();

// Get the current REQUEST_URI
$requestUri = $request->getRequestUri();
$subdirectory = '/compliance/ce';
$publicPath = '/public';

// Remove query string for path processing
$path = parse_url($requestUri, PHP_URL_PATH);
$query = parse_url($requestUri, PHP_URL_QUERY);

// If the path starts with /compliance/ce/public, remove both parts
if (strpos($path, $subdirectory . $publicPath) === 0) {
    $newPath = substr($path, strlen($subdirectory . $publicPath));
    if (empty($newPath)) {
        $newPath = '/';
    }
    
    // Rebuild the REQUEST_URI
    $newUri = $newPath;
    if ($query) {
        $newUri .= '?' . $query;
    }
    
    // Update server variables
    $_SERVER['REQUEST_URI'] = $newUri;
    $_SERVER['PATH_INFO'] = $newPath;
    $_SERVER['SCRIPT_NAME'] = '/compliance/ce/public/index.php';
    $_SERVER['PHP_SELF'] = '/compliance/ce/public/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
    
    // Recapture request with fixed values
    $request = Request::capture();
} 
// If the path starts with /compliance/ce (but not /public), remove just that part
elseif (strpos($path, $subdirectory) === 0) {
    $newPath = substr($path, strlen($subdirectory));
    if (empty($newPath)) {
        $newPath = '/';
    }
    
    // Rebuild the REQUEST_URI
    $newUri = $newPath;
    if ($query) {
        $newUri .= '?' . $query;
    }
    
    // Update server variables
    $_SERVER['REQUEST_URI'] = $newUri;
    $_SERVER['PATH_INFO'] = $newPath;
    $_SERVER['SCRIPT_NAME'] = '/compliance/ce/public/index.php';
    $_SERVER['PHP_SELF'] = '/compliance/ce/public/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
    
    // Recapture request with fixed values
    $request = Request::capture();
}

$app->handleRequest($request);
