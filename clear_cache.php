<?php
// Clear Laravel Cache
$paths = [
    __DIR__ . '/storage/framework/cache',
    __DIR__ . '/bootstrap/cache',
];

foreach ($paths as $path) {
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "Cleared: $path<br>";
    }
}

echo "Cache cleared successfully!<br>";
echo "Now try generating FORM XIII again.";
?>
