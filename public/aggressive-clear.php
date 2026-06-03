<?php
// AGGRESSIVE CACHE CLEAR - Place in public/
// Visit: https://athenas.co.in/compliance/ce/public/aggressive-clear.php

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Aggressive Cache Clear</title>";
echo "<style>body{font-family:monospace;background:#1e293b;color:#e2e8f0;padding:20px;}";
echo ".pass{color:#4ade80;}.fail{color:#f87171;}.warn{color:#fbbf24;}</style>";
echo "</head><body><h1>Aggressive Cache Clearing</h1>";

$base = dirname(dirname(__FILE__));
$dirs = [
    'Storage' => [
        "$base/storage/framework/cache/data" => true,
        "$base/storage/framework/views" => true,
        "$base/storage/framework/sessions" => true,
    ],
    'Route Cache' => [
        "$base/bootstrap/cache/routes-v7.php" => false,
        "$base/bootstrap/cache/routes.php" => false,
    ],
    'Config Cache' => [
        "$base/bootstrap/cache/config.php" => false,
    ],
];

echo "<h2>Clearing Cache Files</h2>";

foreach ($dirs as $section => $paths) {
    echo "<h3>$section</h3>";
    foreach ($paths as $path => $isDir) {
        if ($isDir) {
            // Directory - delete all files
            if (is_dir($path)) {
                $files = glob("$path/*");
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (@unlink($file)) {
                            echo "<span class='pass'>✓</span> Deleted: " . basename($file) . "<br>";
                        } else {
                            echo "<span class='fail'>✗</span> Failed: " . basename($file) . "<br>";
                        }
                    }
                }
            } else {
                echo "<span class='warn'>!</span> Not found: $path<br>";
            }
        } else {
            // File - delete directly
            if (file_exists($path)) {
                if (@unlink($path)) {
                    echo "<span class='pass'>✓</span> Deleted: " . basename($path) . "<br>";
                } else {
                    echo "<span class='fail'>✗</span> Failed to delete: " . basename($path) . "<br>";
                }
            } else {
                echo "<span class='warn'>!</span> Not found: " . basename($path) . "<br>";
            }
        }
    }
}

echo "<h2>Result</h2>";
echo "<p><strong>All cache files have been cleared.</strong></p>";
echo "<p>Now test: <a href='../../login'>Visit /login</a></p>";

echo "</body></html>";
?>
