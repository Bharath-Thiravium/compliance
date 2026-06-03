<?php
// Diagnostic script for Hostinger deployment
$diag = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
];

// Check if index.php exists
$indexPath = __DIR__ . '/index.php';
$diag['index_php_exists'] = file_exists($indexPath);
$diag['index_php_readable'] = is_readable($indexPath);

// Check .env
$envPath = dirname(__DIR__) . '/.env';
$diag['env_exists'] = file_exists($envPath);

// Check app directory
$appPath = dirname(__DIR__) . '/app';
$diag['app_exists'] = is_dir($appPath);

// Check routes
$routesPath = dirname(__DIR__) . '/routes';
$diag['routes_exists'] = is_dir($routesPath);

// Check mod_rewrite
$diag['mod_rewrite_enabled'] = (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) || 'Unknown';

// Check if this is being served from public folder
$diag['served_from_public'] = strpos($_SERVER['SCRIPT_FILENAME'] ?? '', '/public/') !== false;

// Check htaccess
$htaccessRoot = dirname(__DIR__) . '/.htaccess';
$htaccessPublic = __DIR__ . '/.htaccess';
$diag['htaccess_root_exists'] = file_exists($htaccessRoot);
$diag['htaccess_public_exists'] = file_exists($htaccessPublic);

echo '<pre>';
echo json_encode($diag, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo '</pre>';
?>
