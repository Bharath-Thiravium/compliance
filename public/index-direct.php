<?php
// DIRECT ACCESS TEST - Place in e:\CEngine\public\index-direct.php
// Visit: https://athenas.co.in/compliance/ce/public/index-direct.php

echo "<!DOCTYPE html><html><head><title>Direct PHP Test</title>";
echo "<style>body{font-family:sans-serif;padding:20px;}</style>";
echo "</head><body>";

echo "<h1>Direct PHP Access Test</h1>";
echo "<p>If you can see this, PHP is executing correctly.</p>";

echo "<h2>Server Information</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "</pre>";

echo "<h2>Next Steps</h2>";
echo "<p>Test these URLs in order:</p>";
echo "<ol>";
echo "<li><a href='diagnose.php'>diagnose.php</a> - Check file structure</li>";
echo "<li><a href='test-laravel.php'>test-laravel.php</a> - Test Laravel bootstrap</li>";
echo "<li><a href='../index.php'>../index.php</a> - Test through .htaccess forwarding</li>";
echo "<li><a href='../../login'>../../login</a> - Test subdirectory routing</li>";
echo "</ol>";

echo "</body></html>";
?>
