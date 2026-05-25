<?php
/**
 * One-time Hostinger setup script.
 * Run once via browser, then DELETE this file from File Manager.
 * Access: https://athenas.co.in/compliance/ce/setup.php?token=setup_compliance_2026
 */

$secret = 'setup_compliance_2026';

if (!isset($_GET['token']) || !hash_equals($secret, (string) $_GET['token'])) {
    http_response_code(403);
    echo '<h3>403 Unauthorized</h3>';
    echo '<p>Access with <code>?token=setup_compliance_2026</code></p>';
    exit;
}

$root    = dirname(__DIR__);
$artisan = escapeshellarg("$root/artisan");
$results = [];

function runCmd(string $cmd, string $cwd): array
{
    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open($cmd, $descriptors, $pipes, $cwd);
    if (!is_resource($proc)) {
        return ['', 'proc_open failed — exec may be disabled on this host'];
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
    return [trim($stdout), trim($stderr)];
}

function addResult(array &$list, string $step, string $status, string $msg): void
{
    $list[] = compact('step', 'status', 'msg');
}

// ── 1. Check .env ──────────────────────────────────────────────────────────
$envPath = "$root/.env";
if (!file_exists($envPath)) {
    addResult($results, '.env file', 'error',
        '.env not found. Create it via File Manager: copy .env.example → .env and fill in DB credentials.');
} else {
    addResult($results, '.env file', 'ok', 'Found');
}

// ── 2. Check vendor/ ──────────────────────────────────────────────────────
if (!is_dir("$root/vendor")) {
    addResult($results, 'vendor/', 'error',
        'vendor/ directory missing. Run "composer install --no-dev" locally and upload the vendor/ folder.');
} else {
    addResult($results, 'vendor/', 'ok', 'Found');
}

// ── 3. Generate APP_KEY if not set ────────────────────────────────────────
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'APP_KEY=base64:') === false) {
        [$out, $err] = runCmd("php $artisan key:generate --force 2>&1", $root);
        addResult($results, 'APP_KEY generate', $err ? 'error' : 'ok', $out ?: $err);
    } else {
        addResult($results, 'APP_KEY generate', 'skip', 'Already set — skipped');
    }
}

// ── 4. Patch critical .env values ────────────────────────────────────────────
// Auto-detect the base URL from this request so APP_URL / ASSET_URL are correct
// regardless of which subdirectory the app lives in on the server.
if (file_exists($envPath)) {
    $envRaw = file_get_contents($envPath);

    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // SCRIPT_NAME = /compliance/ce/setup.php  →  base = /compliance/ce
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/setup.php'), '/');
    $appUrl   = $scheme . '://' . $host . $basePath;
    $assetUrl = $appUrl . '/public';

    $patches = [
        'APP_ENV'               => 'production',
        'APP_DEBUG'             => 'false',
        'SESSION_SECURE_COOKIE' => 'true',
    ];

    // Only overwrite APP_URL / ASSET_URL when they still point to localhost
    $currentAppUrl = '';
    if (preg_match('/^APP_URL=(.*)$/m', $envRaw, $m)) {
        $currentAppUrl = trim($m[1]);
    }
    if ($currentAppUrl === '' || stripos($currentAppUrl, 'localhost') !== false) {
        $patches['APP_URL']   = $appUrl;
        $patches['ASSET_URL'] = $assetUrl;
    }

    foreach ($patches as $key => $val) {
        if (preg_match('/^' . preg_quote($key, '/') . '=/m', $envRaw)) {
            $envRaw = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $val, $envRaw);
        } else {
            $envRaw = rtrim($envRaw) . "\n" . $key . '=' . $val . "\n";
        }
    }

    file_put_contents($envPath, $envRaw);
    $msgs = array_map(fn($k, $v) => "$k=$v", array_keys($patches), array_values($patches));
    addResult($results, 'Env patch', 'ok', implode(' | ', $msgs));
}

// ── 5. Run migrations ────────────────────────────────────────────────────
[$out, $err] = runCmd("php $artisan migrate --force 2>&1", $root);
$migStatus = (stripos($out . $err, 'error') !== false || stripos($out . $err, 'exception') !== false)
    ? 'error' : 'ok';
addResult($results, 'Migrations', $migStatus, $out ?: $err);

// ── 6. Set directory permissions ──────────────────────────────────────────
$writableDirs = [
    'storage',
    'storage/logs',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/app',
    'storage/app/public',
    'bootstrap/cache',
];

foreach ($writableDirs as $dir) {
    $path = "$root/$dir";
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
    if (is_dir($path)) {
        $ok = chmod($path, 0755);
        addResult($results, "chmod $dir", $ok ? 'ok' : 'warn', $ok ? '755' : 'chmod() failed — set manually via File Manager');
    }
}

// ── 7. Storage symlink (skip if symlink/exec disabled — common on shared hosting) ──
if (function_exists('symlink')) {
    [$out, $err] = runCmd("php $artisan storage:link --force 2>&1", $root);
    $slStatus = (stripos($out . $err, 'error') !== false || $err) ? 'warn' : 'ok';
    addResult($results, 'Storage symlink', $slStatus, $out ?: $err);
} else {
    addResult($results, 'Storage symlink', 'skip', 'symlink() disabled on this host — not needed unless app serves user-uploaded files');
}

// ── 8. Cache config / routes / views ────────────────────────────────────
foreach (['config', 'route', 'view'] as $type) {
    [$out, $err] = runCmd("php $artisan {$type}:cache 2>&1", $root);
    $status = (stripos($out . $err, 'error') !== false) ? 'error' : 'ok';
    addResult($results, "$type:cache", $status, $out ?: $err);
}

// ── Render ────────────────────────────────────────────────────────────────
$hasError = count(array_filter($results, fn($r) => $r['status'] === 'error')) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compliance Engine — Setup</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Courier New', monospace; background: #0f1117; color: #d1d5db; padding: 32px; }
  h1 { color: #f9fafb; margin-bottom: 24px; font-size: 1.25rem; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; padding: 8px 12px; background: #1f2937; color: #9ca3af; font-size: .8rem; text-transform: uppercase; }
  td { padding: 8px 12px; border-bottom: 1px solid #1f2937; vertical-align: top; font-size: .85rem; }
  .ok   { color: #4ade80; font-weight: bold; }
  .warn { color: #facc15; font-weight: bold; }
  .skip { color: #60a5fa; font-weight: bold; }
  .error{ color: #f87171; font-weight: bold; }
  pre { white-space: pre-wrap; word-break: break-all; background: #1a1f2e; padding: 6px 8px; border-radius: 4px; font-size: .8rem; margin-top: 4px; }
  .banner { margin-top: 28px; padding: 14px 18px; border-radius: 6px; font-size: .9rem; }
  .banner-error   { background: #450a0a; border: 1px solid #991b1b; color: #fca5a5; }
  .banner-success { background: #052e16; border: 1px solid #166534; color: #86efac; }
  .banner-delete  { background: #431407; border: 1px solid #9a3412; color: #fed7aa; margin-top: 12px; }
</style>
</head>
<body>
<h1>Compliance Engine &mdash; Hostinger Setup</h1>

<table>
  <thead><tr><th>Step</th><th>Status</th><th>Output</th></tr></thead>
  <tbody>
  <?php foreach ($results as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['step']) ?></td>
    <td class="<?= $r['status'] ?>"><?= strtoupper($r['status']) ?></td>
    <td><pre><?= htmlspecialchars($r['msg']) ?></pre></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php if ($hasError): ?>
<div class="banner banner-error">
  Fix the errors above, then reload this page to re-run setup.
</div>
<?php else: ?>
<div class="banner banner-success">
  Setup complete. Your app should be live at
  <strong>https://athenas.co.in/compliance/ce</strong>
</div>
<?php endif; ?>

<div class="banner banner-delete">
  <strong>Security:</strong> Delete <code>public/setup.php</code> from Hostinger File Manager immediately after this succeeds.
</div>
</body>
</html>
