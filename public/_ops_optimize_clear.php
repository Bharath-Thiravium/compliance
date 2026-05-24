<?php

/**
 * Emergency cache clear endpoint for shared hosts without shell access.
 *
 * Usage:
 * - Create a file next to this one named "_ops_token.txt" containing a long random token.
 * - Visit: https://your-domain/your-app/_ops_optimize_clear.php?token=THE_TOKEN
 * - Delete this file (and _ops_token.txt) immediately after use.
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

header('Content-Type: application/json; charset=UTF-8');

$tokenFile = __DIR__.'/_ops_token.txt';
$expected = is_file($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';
$provided = isset($_GET['token']) ? (string) $_GET['token'] : '';

if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'error' => 'Forbidden',
        'token_file_exists' => is_file($tokenFile),
        'expected_length' => strlen($expected),
        'provided_length' => strlen($provided),
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$exitCode = $kernel->call('optimize:clear');

echo json_encode([
    'ok' => $exitCode === 0,
    'exit_code' => $exitCode,
    'output' => $kernel->output(),
], JSON_UNESCAPED_SLASHES);
