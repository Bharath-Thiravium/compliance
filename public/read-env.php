<?php
/**
 * .ENV File Reader - Shows actual production .env
 * Usage: https://athenas.co.in/compliance/ce/public/read-env.php
 */

$baseDir = dirname(__DIR__);
$envFile = "$baseDir/.env";

header('Content-Type: application/json; charset=utf-8');

$response = [];

if (!file_exists($envFile)) {
    $response['error'] = '.env file not found';
    $response['path'] = $envFile;
    echo json_encode($response);
    exit;
}

// Read raw .env content
$content = file_get_contents($envFile);
$response['file_path'] = $envFile;
$response['file_exists'] = true;
$response['file_size'] = filesize($envFile);
$response['file_readable'] = is_readable($envFile);
$response['file_writable'] = is_writable($envFile);
$response['raw_content'] = $content;

// Parse line by line
$lines = explode("\n", $content);
$response['lines_count'] = count($lines);
$response['parsed'] = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) {
        continue;
    }
    if (strpos($line, '=') !== false) {
        list($key, $val) = explode('=', $line, 2);
        $response['parsed'][trim($key)] = trim($val);
    }
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
