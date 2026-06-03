<?php
// STEP 4: Cache Clearing Script
// Place in public/ and visit: https://athenas.co.in/compliance/ce/clear-caches.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔄 Compliance Engine - Cache Clearing</h1>";
echo "<pre style='background:#1e293b;color:#e2e8f0;padding:20px;border-radius:8px;font-family:monospace;'>";

// Change to app root
chdir(__DIR__.'/..');

// Load Laravel
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Get Artisan
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'optimize:clear',
    'config:clear',
    'route:clear',
    'cache:clear',
    'view:clear',
];

foreach ($commands as $cmd) {
    echo "Running: artisan {$cmd}...\n";
    try {
        $kernel->call($cmd);
        $output = trim($kernel->output());
        echo "✅ {$cmd} completed\n";
        if ($output) echo "   Output: {$output}\n";
    } catch (\Throwable $e) {
        echo "❌ {$cmd} failed: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat('─', 60) . "\n";
echo "Now rebuilding caches...\n";
echo str_repeat('─', 60) . "\n";

$rebuild = [
    'config:cache',
    'route:cache',
    'view:cache',
];

foreach ($rebuild as $cmd) {
    echo "Running: artisan {$cmd}...\n";
    try {
        $kernel->call($cmd);
        $output = trim($kernel->output());
        echo "✅ {$cmd} completed\n";
        if ($output) echo "   Output: {$output}\n";
    } catch (\Throwable $e) {
        echo "❌ {$cmd} failed: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat('─', 60) . "\n";
echo "✅ All caches cleared and rebuilt!\n";
echo str_repeat('─', 60) . "\n";
echo "Next: Visit /login to test\n";

echo "</pre>";
?>
