<?php

/**
 * One-time shared-host repair for workforce_employee employee_code uniqueness.
 *
 * Usage:
 * - Preferred: set OPS_TOKEN in .env and visit:
 *   /public/_ops_fix_employee_index.php?token=YOUR_OPS_TOKEN
 * - Or create public/_ops_token.txt with a long token and use that token.
 * - Delete this file after the repair succeeds.
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=UTF-8');

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$tokenFile = __DIR__.'/_ops_token.txt';
$fileToken = is_file($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';
$envToken = (string) config('app.ops_token', '');
$rawEnvToken = '';
$envPath = $root.'/.env';
if (is_file($envPath)) {
    $envRaw = (string) file_get_contents($envPath);
    if (preg_match('/^\s*(OPS_TOKEN|ops_token)\s*=\s*(.*)\s*$/mi', $envRaw, $match)) {
        $rawEnvToken = trim($match[2], " \t\n\r\0\x0B\"'");
    }
}
$expected = $fileToken !== '' ? $fileToken : ($envToken !== '' ? $envToken : $rawEnvToken);
$provided = isset($_GET['token']) ? (string) $_GET['token'] : '';

function renderPage(array $rows, int $statusCode = 200): void
{
    http_response_code($statusCode);
    $hasError = count(array_filter($rows, fn ($row) => $row['status'] === 'error')) > 0;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Code Index Repair</title>
<style>
body{font-family:Arial,sans-serif;background:#10141f;color:#e5e7eb;padding:28px}
h1{font-size:22px;margin:0 0 18px}
table{border-collapse:collapse;width:100%;background:#171d2b}
th,td{border-bottom:1px solid #293244;padding:10px 12px;text-align:left;vertical-align:top}
th{background:#222b3d;color:#aeb8c8;font-size:12px;text-transform:uppercase}
.ok{color:#4ade80;font-weight:700}.skip{color:#60a5fa;font-weight:700}.error{color:#f87171;font-weight:700}
pre{white-space:pre-wrap;margin:0;font-family:Consolas,monospace;font-size:13px}
.banner{margin-top:18px;padding:12px 14px;border-radius:6px}
.success{background:#052e16;color:#bbf7d0}.danger{background:#450a0a;color:#fecaca}.warn{background:#431407;color:#fed7aa}
</style>
</head>
<body>
<h1>Employee Code Index Repair</h1>
<table>
<thead><tr><th>Step</th><th>Status</th><th>Output</th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= htmlspecialchars($row['step']) ?></td>
<td class="<?= htmlspecialchars($row['status']) ?>"><?= strtoupper(htmlspecialchars($row['status'])) ?></td>
<td><pre><?= htmlspecialchars($row['message']) ?></pre></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if ($hasError): ?>
<div class="banner danger">Repair did not complete. Fix the error shown above, then reload this page.</div>
<?php else: ?>
<div class="banner success">Done. Now upload employees.csv again, then payroll and attendance.</div>
<?php endif; ?>
<div class="banner warn">Security: delete <code>public/_ops_fix_employee_index.php</code> after this succeeds.</div>
</body>
</html>
<?php
}

if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
    renderPage([[
        'step' => 'Token check',
        'status' => 'error',
        'message' => 'Forbidden. Set OPS_TOKEN in .env or create public/_ops_token.txt, then pass ?token=YOUR_TOKEN.',
    ]], 403);
    exit;
}

$rows = [];
$add = function (string $step, string $status, string $message) use (&$rows): void {
    $rows[] = compact('step', 'status', 'message');
};

try {
    DB::connection()->getPdo();
    $add('Database', 'ok', 'Connected to '.config('database.connections.mysql.database'));

    $duplicates = DB::table('workforce_employee')
        ->select('tenant_id', 'employee_code', DB::raw('COUNT(*) as total'))
        ->groupBy('tenant_id', 'employee_code')
        ->havingRaw('COUNT(*) > 1')
        ->limit(20)
        ->get();

    if ($duplicates->isNotEmpty()) {
        $add('Duplicate check', 'error', $duplicates->map(
            fn ($row) => "tenant_id={$row->tenant_id}, employee_code={$row->employee_code}, total={$row->total}"
        )->implode("\n"));
        renderPage($rows, 409);
        exit;
    }

    $add('Duplicate check', 'ok', 'No duplicate employee_code values inside the same tenant.');

    $indexes = collect(DB::select('SHOW INDEX FROM `workforce_employee`'))
        ->pluck('Key_name')
        ->unique()
        ->values();

    if ($indexes->contains('workforce_employee_employee_code_unique')) {
        DB::statement('ALTER TABLE `workforce_employee` DROP INDEX `workforce_employee_employee_code_unique`');
        $add('Drop old index', 'ok', 'Dropped global employee_code unique index.');
    } else {
        $add('Drop old index', 'skip', 'Old global employee_code index was not present.');
    }

    $indexes = collect(DB::select('SHOW INDEX FROM `workforce_employee`'))
        ->pluck('Key_name')
        ->unique()
        ->values();

    if (! $indexes->contains('workforce_employee_tenant_employee_code_unique')) {
        DB::statement(
            'ALTER TABLE `workforce_employee` ADD UNIQUE KEY `workforce_employee_tenant_employee_code_unique` (`tenant_id`, `employee_code`)'
        );
        $add('Add tenant index', 'ok', 'Added unique index on tenant_id + employee_code.');
    } else {
        $add('Add tenant index', 'skip', 'Tenant-scoped unique index already exists.');
    }

    try {
        $kernel->call('optimize:clear');
        $add('Cache clear', 'ok', trim($kernel->output()) ?: 'Laravel cache cleared.');
    } catch (Throwable $e) {
        $add('Cache clear', 'skip', $e->getMessage());
    }
} catch (Throwable $e) {
    $add('Repair failed', 'error', $e->getMessage());
    renderPage($rows, 500);
    exit;
}

renderPage($rows);
