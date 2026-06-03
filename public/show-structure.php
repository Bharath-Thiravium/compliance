<?php
// DIRECTORY STRUCTURE VIEWER
// Place at: /public_html/compliance/ce/public/show-structure.php

$appRoot = dirname(__DIR__);

echo "<!DOCTYPE html><html><head><title>Directory Structure</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e293b;color:#e2e8f0;}";
echo "pre{background:#0f172a;padding:15px;border-radius:5px;overflow-x:auto;}</style>";
echo "</head><body>";

echo "<h1>Directory Structure at: $appRoot</h1>";

echo "<h2>Direct Contents:</h2>";
echo "<pre>";

$items = scandir($appRoot);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    
    $path = "$appRoot/$item";
    if (is_dir($path)) {
        echo "📁 $item/\n";
        
        // Show subdirectories if it's a common folder
        if (in_array($item, ['app', 'bootstrap', 'config', 'public', 'routes', 'storage', 'vendor'])) {
            $subItems = @scandir($path);
            if ($subItems) {
                echo "   Contents:\n";
                foreach ($subItems as $subItem) {
                    if ($subItem !== '.' && $subItem !== '..') {
                        $subPath = "$path/$subItem";
                        if (is_dir($subPath)) {
                            echo "     📁 $subItem/\n";
                        } else {
                            echo "     📄 $subItem\n";
                        }
                    }
                }
            }
        }
    } else {
        $size = filesize($path);
        echo "📄 $item ($size bytes)\n";
    }
}

echo "</pre>";

echo "<h2>Checking for Nested Structure Issues:</h2>";

// Check if everything is nested one level too deep
$possibleNestedPaths = [
    "$appRoot/compliance/ce/app",
    "$appRoot/compliance/ce/vendor",
    "$appRoot/compliance/ce/.env",
];

echo "<ul>";
foreach ($possibleNestedPaths as $path) {
    $exists = file_exists($path) || is_dir($path);
    $status = $exists ? "✓ EXISTS" : "✗ Not there";
    echo "<li>$status: $path</li>";
}
echo "</ul>";

echo "<h2>What Should Happen:</h2>";
echo "<p>Files should be directly in: /public_html/compliance/ce/</p>";
echo "<p>NOT in: /public_html/compliance/ce/compliance/ce/</p>";

echo "</body></html>";
?>
