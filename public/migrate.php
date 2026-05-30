<?php
/**
 * Run migrations via browser.
 * Access: https://athenas.co.in/compliance/ce/public/migrate.php?token=migrate_compliance_2026
 * DELETE this file after use.
 */

if (!isset($_GET['token']) || !hash_equals('migrate_compliance_2026', (string) $_GET['token'])) {
    http_response_code(403);
    die('<h3>403 Unauthorized</h3>');
}

$root    = dirname(__DIR__);
$artisan = escapeshellarg("$root/artisan");

$descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$proc = proc_open("php $artisan migrate --force 2>&1", $descriptors, $pipes, $root);

$output = is_resource($proc) ? stream_get_contents($pipes[1]) : 'proc_open failed — exec may be disabled.';
if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }

$success = stripos($output, 'error') === false && stripos($output, 'exception') === false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Migration</title>
<style>
  body { font-family: monospace; background: #0f1117; color: #d1d5db; padding: 32px; }
  pre  { background: #1a1f2e; padding: 16px; border-radius: 6px; white-space: pre-wrap; }
  .ok  { color: #4ade80; } .err { color: #f87171; }
  .warn { margin-top: 20px; background: #431407; border: 1px solid #9a3412;
          color: #fed7aa; padding: 12px 16px; border-radius: 6px; }
</style>
</head>
<body>
<h2 class="<?= $success ? 'ok' : 'err' ?>"><?= $success ? '✅ Migration Complete' : '❌ Migration Failed' ?></h2>
<pre><?= htmlspecialchars($output) ?></pre>
<div class="warn"><strong>Security:</strong> Delete <code>public/migrate.php</code> immediately after use.</div>
</body>
</html>
