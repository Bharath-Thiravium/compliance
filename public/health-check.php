<?php
/**
 * Minimal Health Check - No Laravel booting
 */

$baseDir = dirname(__DIR__);
$report = [];

// 1. File System
$report['files_exist'] = [
    'vendor_autoload' => file_exists("$baseDir/vendor/autoload.php"),
    'bootstrap_app' => file_exists("$baseDir/bootstrap/app.php"),
    'routes_web' => file_exists("$baseDir/routes/web.php"),
    'env' => file_exists("$baseDir/.env"),
];

// 2. .env parse
$envContent = file_get_contents("$baseDir/.env");
$envLines = array_filter(array_map('trim', explode("\n", $envContent)));
$report['env_lines'] = count($envLines);
$report['app_url'] = null;
$report['app_env'] = null;
$report['db_database'] = null;

foreach ($envLines as $line) {
    if (strpos($line, 'APP_URL=') === 0) $report['app_url'] = trim(str_replace('APP_URL=', '', $line));
    if (strpos($line, 'APP_ENV=') === 0) $report['app_env'] = trim(str_replace('APP_ENV=', '', $line));
    if (strpos($line, 'DB_DATABASE=') === 0) $report['db_database'] = trim(str_replace('DB_DATABASE=', '', $line));
}

// 3. Route files content check
$webphpContent = file_get_contents("$baseDir/routes/web.php");
$report['web_php'] = [
    'size' => strlen($webphpContent),
    'has_route_get_login' => strpos($webphpContent, "Route::get('/login'") !== false,
    'has_compliance_require' => strpos($webphpContent, "require __DIR__.'/compliance.php'") !== false,
    'has_diagnostics_require' => strpos($webphpContent, "require __DIR__.'/diagnostics.php'") !== false,
];

// 4. Check for syntax errors
$report['syntax_check'] = [];
$files_to_check = [
    'routes/web.php',
    'routes/compliance.php',
    'bootstrap/app.php',
];

foreach ($files_to_check as $file) {
    $path = "$baseDir/$file";
    if (file_exists($path)) {
        $output = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
        $report['syntax_check'][$file] = strpos($output, 'No syntax errors') !== false ? 'OK' : 'ERROR: ' . $output;
    }
}

// 5. Storage directories
$report['storage_dirs'] = [
    'logs' => is_dir("$baseDir/storage/logs") && is_writable("$baseDir/storage/logs"),
    'cache' => is_dir("$baseDir/storage/framework/cache") && is_writable("$baseDir/storage/framework/cache"),
    'framework' => is_dir("$baseDir/storage/framework") && is_writable("$baseDir/storage/framework"),
];

// 6. Recent log (last 20 lines)
$logFile = "$baseDir/storage/logs/laravel.log";
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20);
    $report['recent_logs'] = array_map('trim', $lines);
}

$report['status'] = 'System check complete - Review carefully';

header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
