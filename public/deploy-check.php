<?php
// Deployment Verification Script for Hostinger
// Place in public/ and visit: https://athenas.co.in/compliance/ce/deploy-check.php

echo "<h1>🚀 Compliance Engine - Deployment Verification</h1>";
echo "<pre style='background:#1e293b;color:#e2e8f0;padding:20px;border-radius:8px;font-family:monospace;'>";

$checks = [];

// Check 1: PHP Version
$checks['php_version'] = [
    'label' => 'PHP Version',
    'value' => PHP_VERSION,
    'pass' => version_compare(PHP_VERSION, '8.0', '>=')
];

// Check 2: Document Root
$checks['document_root'] = [
    'label' => 'Document Root',
    'value' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'pass' => strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'compliance') !== false
];

// Check 3: Script Filename
$checks['script_filename'] = [
    'label' => 'Script Location',
    'value' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
    'pass' => strpos($_SERVER['SCRIPT_FILENAME'] ?? '', 'public') !== false
];

// Check 4: Request URI
$checks['request_uri'] = [
    'label' => 'Request Path',
    'value' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
    'pass' => true
];

// Check 5: .env file
$envPath = __DIR__.'/../.env';
$checks['env_exists'] = [
    'label' => '.env File',
    'value' => file_exists($envPath) ? 'EXISTS' : 'MISSING',
    'pass' => file_exists($envPath)
];

// Check 6: vendor directory
$vendorPath = __DIR__.'/../vendor';
$checks['vendor_exists'] = [
    'label' => 'Vendor Directory',
    'value' => is_dir($vendorPath) ? 'EXISTS' : 'MISSING',
    'pass' => is_dir($vendorPath)
];

// Check 7: Autoloader
$autoloadPath = __DIR__.'/../vendor/autoload.php';
$checks['autoloader_exists'] = [
    'label' => 'Composer Autoloader',
    'value' => file_exists($autoloadPath) ? 'EXISTS' : 'MISSING',
    'pass' => file_exists($autoloadPath)
];

// Check 8: Bootstrap
$bootstrapPath = __DIR__.'/../bootstrap/app.php';
$checks['bootstrap_exists'] = [
    'label' => 'Bootstrap File',
    'value' => file_exists($bootstrapPath) ? 'EXISTS' : 'MISSING',
    'pass' => file_exists($bootstrapPath)
];

// Check 9: app directory
$checks['app_exists'] = [
    'label' => 'App Directory',
    'value' => is_dir(__DIR__.'/../app') ? 'EXISTS' : 'MISSING',
    'pass' => is_dir(__DIR__.'/../app')
];

// Check 10: Storage permissions
$checks['storage_writable'] = [
    'label' => 'Storage Writable',
    'value' => is_writable(__DIR__.'/../storage') ? 'YES' : 'NO',
    'pass' => is_writable(__DIR__.'/../storage')
];

// Check 11: mod_rewrite
$checks['mod_rewrite'] = [
    'label' => 'mod_rewrite Enabled',
    'value' => (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) ? 'YES' : 'UNKNOWN',
    'pass' => true
];

// Check 12: .htaccess files
$htRoot = __DIR__.'/../.htaccess';
$htPublic = __DIR__.'/.htaccess';
$checks['htaccess_root'] = [
    'label' => 'Root .htaccess',
    'value' => file_exists($htRoot) ? 'EXISTS' : 'MISSING',
    'pass' => file_exists($htRoot)
];

$checks['htaccess_public'] = [
    'label' => 'Public .htaccess',
    'value' => file_exists($htPublic) ? 'EXISTS' : 'MISSING',
    'pass' => file_exists($htPublic)
];

// Display results
foreach ($checks as $key => $check) {
    $status = $check['pass'] ? '✅ PASS' : '❌ FAIL';
    $value = htmlspecialchars($check['value']);
    echo "{$status} | {$check['label']}: {$value}\n";
}

// Try to bootstrap Laravel
echo "\n" . str_repeat('─', 60) . "\n";
echo "LARAVEL BOOTSTRAP TEST\n";
echo str_repeat('─', 60) . "\n";

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    echo "✅ Laravel bootstrapped successfully\n";
    echo "✅ Application Environment: " . env('APP_ENV') . "\n";
    echo "✅ Application URL: " . config('app.url') . "\n";
    echo "✅ Debug Mode: " . (config('app.debug') ? 'ENABLED' : 'DISABLED') . "\n";
    
} catch (\Throwable $e) {
    echo "❌ Laravel bootstrap failed:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n" . str_repeat('─', 60) . "\n";
echo "NEXT STEPS:\n";
echo str_repeat('─', 60) . "\n";
echo "1. If all checks pass: Try visiting /login\n";
echo "2. If Laravel bootstrap fails: Check .env and vendor/\n";
echo "3. If .htaccess missing: Verify files were uploaded\n";
echo "4. If mod_rewrite unknown: Contact Hostinger support\n";

echo "</pre>";
?>
