<?php
/**
 * Complete Manual Cache Cleaner
 * Usage: https://athenas.co.in/compliance/ce/public/nuke-cache.php?token=ops_compliance_2026
 */

$validToken = 'ops_compliance_2026';
$providedToken = $_GET['token'] ?? '';

if ($providedToken !== $validToken) {
    http_response_code(403);
    die('{"error": "Unauthorized"}');
}

$baseDir = dirname(__DIR__);
$output = [];
$totalDeleted = 0;

// Define all cache directories to clean
$cacheDirs = [
    // Bootstrap cache
    $baseDir . '/bootstrap/cache' => 'Bootstrap Cache',
    // Storage framework
    $baseDir . '/storage/framework/cache' => 'Storage Cache',
    $baseDir . '/storage/framework/views' => 'Storage Views',
    $baseDir . '/storage/framework/sessions' => 'Storage Sessions',
    // Laravel's default cache locations
    $baseDir . '/storage/cache' => 'Storage Cache (alt)',
];

foreach ($cacheDirs as $dir => $label) {
    $deleted = 0;
    $status = 'NOT FOUND';
    
    if (is_dir($dir)) {
        $status = 'FOUND';
        
        // Get all files
        $files = @glob($dir . '/*');
        
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (@unlink($file)) {
                        $deleted++;
                        $totalDeleted++;
                    }
                }
            }
        }
        
        $status = "DELETED $deleted FILES";
    }
    
    $output[$label] = [
        'path' => $dir,
        'status' => $status,
        'files_deleted' => $deleted,
    ];
}

// Try to delete specific cache files if they exist
$cacheFiles = [
    $baseDir . '/bootstrap/cache/config.php',
    $baseDir . '/bootstrap/cache/routes-v7.php',
    $baseDir . '/bootstrap/cache/services.php',
    $baseDir . '/storage/logs/laravel.log',
];

$output['specific_files'] = [];
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        if (@unlink($file)) {
            $output['specific_files'][$file] = "✓ DELETED (" . $size . " bytes)";
            $totalDeleted++;
        } else {
            $output['specific_files'][$file] = "✗ FAILED TO DELETE";
        }
    }
}

$output['summary'] = [
    'total_files_deleted' => $totalDeleted,
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => 'COMPLETE',
    'next_step' => 'Visit https://athenas.co.in/compliance/ce/login and try logging in',
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
