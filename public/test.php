<?php
// This file should be accessible at /compliance/ce/public/test.php
// If you can see this, .htaccess is working

file_put_contents(dirname(__DIR__) . '/test-marker.txt', 'Test marker - ' . date('Y-m-d H:i:s') . "\n");

echo json_encode([
    'status' => 'OK',
    'file' => __FILE__,
    'message' => 'If you see this, public/test.php is being served',
    'timestamp' => date('Y-m-d H:i:s'),
]);
