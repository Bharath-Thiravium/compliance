<?php
// COMPOSER FIXER
// Place at: /public_html/compliance/ce/public/fix-composer.php

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';

echo "<!DOCTYPE html><html><head><title>Composer Fixer</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo ".ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;} pre{background:#0f172a;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>Composer Autoloader Fixer</h1>";

// Check if composer exists
$composerPath = "$appRoot/vendor/bin/composer";
if (!file_exists($composerPath)) {
    $composerPath = "/usr/local/bin/composer";
    if (!file_exists($composerPath)) {
        $composerPath = "composer";
    }
}

echo "<p>Running: composer dump-autoload -o</p>";
echo "<pre>";

// Change to app directory
chdir($appRoot);

// Run composer dump-autoload
$output = shell_exec("cd " . escapeshellarg($appRoot) . " && composer dump-autoload -o 2>&1");
echo htmlspecialchars($output);

echo "</pre>";

if (strpos($output, 'Generating optimized autoload files') !== false || strpos($output, 'Generated optimized autoload files') !== false) {
    echo "<p><span class='ok'>✓ Composer autoloader regenerated successfully!</span></p>";
    echo "<p>Now try visiting: <a href='../../login'>/login</a></p>";
} else {
    echo "<p><span class='err'>✗ Something went wrong. Check output above.</span></p>";
}

echo "</body></html>";
?>
