<?php
header('Content-Type: application/json; charset=UTF-8');

$envFile = __DIR__.'/../.env';
$expected = '';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'OPS_TOKEN=') === 0) {
            $expected = trim(substr($line, 10));
            break;
        }
    }
}
$provided = isset($_GET['token']) ? (string) $_GET['token'] : '';

if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'error' => 'Forbidden',
        'env_file_exists' => is_file($envFile),
        'expected_length' => strlen($expected),
        'provided_length' => strlen($provided),
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);

$exitCode = $kernel->call('optimize:clear');

echo json_encode([
    'ok' => $exitCode === 0,
    'exit_code' => $exitCode,
    'output' => $kernel->output(),
], JSON_UNESCAPED_SLASHES);
