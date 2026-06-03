<?php
/**
 * Cache Clearing Script - Uses Laravel Artisan Directly
 * Usage: https://athenas.co.in/compliance/ce/public/clear-cache-direct.php?token=YOUR_TOKEN
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
    // Bootstrap Laravel
    require_once "$baseDir/vendor/autoload.php";
    $app = require_once "$baseDir/bootstrap/app.php";
    
    $output['status_bootstrap'] = '✓ Laravel bootstrapped';
    
    // Get the Artisan kernel
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $output['status_kernel'] = '✓ Kernel created';
    
    // 1. Optimize Clear
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output['step_1_optimize_clear'] = '✓ ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $output['step_1_optimize_clear'] = '✗ ' . $e->getMessage();
    }
    
    // 2. Route Clear
    try {
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        $output['step_2_route_clear'] = '✓ ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $output['step_2_route_clear'] = '✗ ' . $e->getMessage();
    }
    
    // 3. Config Clear
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        $output['step_3_config_clear'] = '✓ ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $output['step_3_config_clear'] = '✗ ' . $e->getMessage();
    }
    
    // 4. Cache Clear
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $output['step_4_cache_clear'] = '✓ ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $output['step_4_cache_clear'] = '✗ ' . $e->getMessage();
    }
    
    // 5. View Clear
    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $output['step_5_view_clear'] = '✓ ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $output['step_5_view_clear'] = '✗ ' . $e->getMessage();
    }
    
    // 6. Manual file cleanup
    $output['step_6_manual_cleanup'] = [];
    $cacheDirs = [
        $baseDir . '/bootstrap/cache',
        $baseDir . '/storage/framework/cache',
        $baseDir . '/storage/framework/views',
    ];
    
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            $deleted = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (@unlink($file)) {
                        $deleted++;
                    }
                }
            }
            $output['step_6_manual_cleanup'][$dir] = "✓ Deleted $deleted files";
        }
    }
    
    $output['status'] = 'SUCCESS - All caches cleared!';
    $output['timestamp'] = date('Y-m-d H:i:s');
    
} catch (\Throwable $e) {
    http_response_code(500);
    $output['error'] = $e->getMessage();
    $output['file'] = $e->getFile();
    $output['line'] = $e->getLine();
    $output['status'] = 'FAILED';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
