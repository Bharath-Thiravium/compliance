<?php
// This file should be accessible at /compliance/ce/test.php
// If you can see this, .htaccess is working correctly

file_put_contents(dirname(__FILE__) . '/test-marker.txt', 'Test marker - ' . date('Y-m-d H:i:s') . "\n");

echo json_encode([
    'status' => 'OK',
    'file' => __FILE__,
    'message' => 'If you see this, root test.php is being served - deployment successful!',
    'timestamp' => date('Y-m-d H:i:s'),
], JSON_PRETTY_PRINT);
