<?php
echo "Test 1: PHP is working\n";
flush();

$appRoot = '/home/u494785662/domains/athenas.co.in/public_html/compliance/ce';
echo "App root: $appRoot\n";
flush();

// Check if files exist
echo "\nFile checks:\n";
$files = ['routes/web.php', 'routes/compliance.php', 'bootstrap/app.php'];
foreach ($files as $file) {
    $path = "$appRoot/$file";
    $exists = file_exists($path);
    echo ($exists ? "✓" : "✗") . " $file\n";
    flush();
}

// Try to load web.php directly
echo "\nTrying to load routes/web.php:\n";
try {
    ob_start();
    include "$appRoot/routes/web.php";
    $output = ob_get_clean();
    echo "✓ routes/web.php loaded successfully\n";
} catch (Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nEnd of test\n";
?>
