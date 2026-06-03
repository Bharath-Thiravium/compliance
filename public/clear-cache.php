<?php
/**
 * Cache Clearing Script
 * Usage: https://athenas.co.in/compliance/ce/public/clear-cache.php?token=YOUR_TOKEN
 * 
 * Token is: ops_compliance_2026 (from your .env OPS_TOKEN)
 */

$validToken = 'ops_compliance_2026';
$providedToken = $_GET['token'] ?? '';

if ($providedToken !== $validToken) {
    http_response_code(403);
    die('Unauthorized: Invalid token');
}

$baseDir = dirname(__DIR__);
$output = [];

try {
    // Change to app directory
    chdir($baseDir);
    
    // 1. Optimize Clear
    $output['step_1_optimize_clear'] = shell_exec('php artisan optimize:clear 2>&1');
    
    // 2. Route Clear
    $output['step_2_route_clear'] = shell_exec('php artisan route:clear 2>&1');
    
    // 3. Config Clear
    $output['step_3_config_clear'] = shell_exec('php artisan config:clear 2>&1');
    
    // 4. Cache Clear
    $output['step_4_cache_clear'] = shell_exec('php artisan cache:clear 2>&1');
    
    // 5. View Clear
    $output['step_5_view_clear'] = shell_exec('php artisan view:clear 2>&1');
    
    // 6. Manual cache directory cleanup
    $cacheDirs = [
        $baseDir . '/bootstrap/cache',
        $baseDir . '/storage/framework/cache',
        $baseDir . '/storage/framework/views',
    ];
    
    $output['step_6_manual_cleanup'] = [];
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $output['step_6_manual_cleanup'][$dir] = 'Cleaned';
        }
    }
    
    $output['status'] = 'SUCCESS - All caches cleared!';
    $output['timestamp'] = date('Y-m-d H:i:s');
    
} catch (\Throwable $e) {
    http_response_code(500);
    $output['error'] = $e->getMessage();
    $output['status'] = 'FAILED';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
