<?php
/**
 * Root Router for Subdirectory Deployment
 * 
 * This file is at: /compliance/ce/index.php
 * It routes all requests to: /compliance/ce/public/index.php
 * 
 * This works around .htaccess limitations on Hostinger
 */

// Only process if not already in public folder
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';

// If we're already in public folder, don't redirect
if (strpos($scriptFilename, '/public/') !== false) {
    require_once __DIR__ . '/public/index.php';
    exit;
}

// Check if this is a direct request to public folder files
if (preg_match('~^/compliance/ce/(css|js|images|storage)/~', $requestUri)) {
    // Serve static files directly
    $file = __DIR__ . str_replace('/compliance/ce/', '/', $requestUri);
    if (file_exists($file) && is_file($file)) {
        // Let web server serve it
        return false;
    }
}

// Route everything else through public/index.php
require_once __DIR__ . '/public/index.php';
