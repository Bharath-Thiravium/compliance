<?php
/**
 * Log Reader - Shows Laravel errors
 */

$baseDir = dirname(__DIR__);
$logFile = "$baseDir/storage/logs/laravel.log";

header('Content-Type: text/plain; charset=utf-8');

if (!file_exists($logFile)) {
    echo "Log file not found at: $logFile\n";
    exit;
}

// Read last 100 lines
$lines = array_slice(file($logFile), -100);
echo "=== LAST 100 LINES OF LARAVEL LOG ===\n\n";
echo implode("", $lines);
