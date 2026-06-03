<?php
// DATABASE CONNECTION TEST
// Place at: /public_html/compliance/ce/public/test-db.php

$appRoot = dirname(__DIR__);

echo "<!DOCTYPE html><html><head><title>Database Connection Test</title>";
echo "<style>body{font-family:sans-serif;padding:20px;} .pass{color:green;} .fail{color:red;}</style>";
echo "</head><body><h1>Database Connection Test</h1>";

// Load .env
$envFile = "$appRoot/.env";
if (!file_exists($envFile)) {
    echo "<p class='fail'>✗ .env file not found at: $envFile</p>";
    exit;
}

echo "<p class='pass'>✓ .env file found</p>";

// Parse .env
$env = parse_ini_file($envFile);

echo "<h2>Database Configuration (from .env):</h2>";
echo "<pre>";
echo "DB_CONNECTION: " . ($env['DB_CONNECTION'] ?? 'NOT SET') . "\n";
echo "DB_HOST: " . ($env['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_PORT: " . ($env['DB_PORT'] ?? 'NOT SET') . "\n";
echo "DB_DATABASE: " . ($env['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "DB_USERNAME: " . ($env['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "DB_PASSWORD: " . (isset($env['DB_PASSWORD']) ? '***' : 'NOT SET') . "\n";
echo "</pre>";

// Check for placeholder password
if (strpos($env['DB_PASSWORD'] ?? '', '<') !== false) {
    echo "<p class='fail'><strong>✗ PROBLEM FOUND:</strong></p>";
    echo "<p>DB_PASSWORD has placeholder value: <code>" . htmlspecialchars($env['DB_PASSWORD']) . "</code></p>";
    echo "<p>You must set the ACTUAL database password in .env</p>";
    exit;
}

// Try to connect
echo "<h2>Testing Connection:</h2>";

try {
    $host = $env['DB_HOST'] ?? 'localhost';
    $db = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p class='pass'>✓ Database connection successful!</p>";
    
    // Check for required tables
    $result = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$db'");
    $tableCount = $result->fetchColumn();
    echo "<p>Tables in database: $tableCount</p>";
    
} catch (PDOException $e) {
    echo "<p class='fail'>✗ Database connection failed:</p>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Check:</strong></p>";
    echo "<ul>";
    echo "<li>Database host is correct</li>";
    echo "<li>Database name is correct</li>";
    echo "<li>Username is correct</li>";
    echo "<li>Password is correct</li>";
    echo "<li>Database server is running</li>";
    echo "</ul>";
}

echo "</body></html>";
?>
