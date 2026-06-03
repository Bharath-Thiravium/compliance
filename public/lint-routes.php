<?php
// PHP SYNTAX LINTER
// Place at: /public_html/compliance/ce/public/lint-routes.php

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>PHP Syntax Lint</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo ".ok{color:#4ade80;} .err{color:#f87171;} pre{background:#0f172a;padding:10px;border-radius:5px;overflow-x:auto;}</style>";
echo "</head><body>";

echo "<h1>PHP Syntax Check for Route Files</h1>";

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

$hasErrors = false;

foreach ($files as $file) {
    $path = "$appRoot/$file";
    
    if (!file_exists($path)) {
        echo "<span class='err'>✗ MISSING:</span> $file<br>";
        $hasErrors = true;
        continue;
    }
    
    // Use php to lint the file
    $output = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
    
    if (strpos($output, 'No syntax errors detected') !== false) {
        echo "<span class='ok'>✓</span> $file<br>";
    } else {
        echo "<span class='err'>✗ ERROR in $file:</span><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        $hasErrors = true;
    }
}

if (!$hasErrors) {
    echo "<br><span class='ok'>✓ All files have valid PHP syntax!</span>";
} else {
    echo "<br><span class='err'>✗ Some files have syntax errors - see above</span>";
}

echo "</body></html>";
?>
