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

// STEP 1: Fix REQUEST_URI for subdirectory deployment
$uri = $_SERVER['REQUEST_URI'] ?? '';
$basePath = '/compliance/ce';

if (str_starts_with($uri, $basePath)) {
    $_SERVER['REQUEST_URI'] = substr($uri, strlen($basePath)) ?: '/';
}

if (!str_starts_with($_SERVER['REQUEST_URI'], '/')) {
    $_SERVER['REQUEST_URI'] = '/' . $_SERVER['REQUEST_URI'];
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
