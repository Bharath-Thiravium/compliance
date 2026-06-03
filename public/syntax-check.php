<?php
// SYNTAX CHECKER
// Place at: /public_html/compliance/ce/public/syntax-check.php

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>Syntax Check</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo ".ok{color:#4ade80;} .err{color:#f87171;} pre{background:#0f172a;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>PHP Syntax Check</h1>";

$files = [
    'routes/web.php',
    'routes/compliance.php',
    'routes/batch-processing.php',
    'routes/data-input.php',
    'routes/super-admin.php',
    'routes/smart-templates.php',
    'routes/api.php',
    'routes/console.php',
];

foreach ($files as $file) {
    $path = "$appRoot/$file";
    if (!file_exists($path)) {
        echo "<span class='err'>✗</span> NOT FOUND: $file<br>";
        continue;
    }
    
    $output = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
    
    if (strpos($output, 'No syntax errors') !== false) {
        echo "<span class='ok'>✓</span> OK: $file<br>";
    } else {
        echo "<span class='err'>✗</span> ERROR in $file:<br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
}

echo "</body></html>";
?>
