<?php
// DEEP DIAGNOSTIC - Place in e:\CEngine\public\diagnose.php
// Visit: https://athenas.co.in/compliance/ce/public/diagnose.php (bypass .htaccess)

header('Content-Type: text/plain');

echo "=== HOSTINGER DEPLOYMENT DIAGNOSTIC ===\n\n";

echo "SERVER VARIABLES:\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'NOT SET') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";

echo "\n--- PATH ANALYSIS ---\n";
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? '';
$reqUri = $_SERVER['REQUEST_URI'] ?? '';

echo "Is script in /public? " . (strpos($scriptFile, '/public/') !== false ? "YES" : "NO") . "\n";
echo "Is doc root correct? " . (strpos($docRoot, 'compliance/ce') !== false ? "YES" : "NO") . "\n";

echo "\n--- FILE EXISTENCE ---\n";
$baseDir = dirname(dirname(__DIR__));
echo "Base dir: $baseDir\n";
echo ".env exists? " . (file_exists("$baseDir/.env") ? "YES" : "NO") . "\n";
echo "vendor exists? " . (is_dir("$baseDir/vendor") ? "YES" : "NO") . "\n";
echo "app exists? " . (is_dir("$baseDir/app") ? "YES" : "NO") . "\n";
echo "bootstrap exists? " . (is_dir("$baseDir/bootstrap") ? "YES" : "NO") . "\n";

echo "\n--- HTACCESS CHECK ---\n";
echo "Root .htaccess exists? " . (file_exists("$baseDir/.htaccess") ? "YES" : "NO") . "\n";
echo "Public .htaccess exists? " . (file_exists(__DIR__ . '/.htaccess') ? "YES" : "NO") . "\n";

if (file_exists("$baseDir/.htaccess")) {
    echo "\nRoot .htaccess content:\n";
    echo file_get_contents("$baseDir/.htaccess");
}

echo "\n--- APACHE MOD_REWRITE CHECK ---\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite enabled? " . (in_array('mod_rewrite', $modules) ? "YES" : "NO/UNKNOWN") . "\n";
    echo "\nAll modules: " . implode(', ', $modules) . "\n";
} else {
    echo "Cannot determine Apache modules (apache_get_modules not available)\n";
}

echo "\n--- BOOTSTRAP TEST ---\n";
try {
    require "$baseDir/vendor/autoload.php";
    $app = require_once "$baseDir/bootstrap/app.php";
    echo "✅ Laravel bootstrapped successfully\n";
    echo "APP_URL: " . config('app.url') . "\n";
    echo "APP_ENV: " . config('app.env') . "\n";
} catch (Throwable $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
}

echo "\n--- RECOMMENDATION ---\n";
echo "Visit: https://athenas.co.in/compliance/ce/public/diagnose.php\n";
echo "This bypasses .htaccess to see actual server state\n";
?>
