<?php
/**
 * Deletes sensitive scripts from the live server.
 * Access: https://athenas.co.in/compliance/ce/public/cleanup.php?token=cleanup_compliance_2026
 * This file deletes itself last.
 */

if (!isset($_GET['token']) || !hash_equals('cleanup_compliance_2026', (string) $_GET['token'])) {
    http_response_code(403);
    die('<h3>403 Unauthorized</h3>');
}

$files = [
    __DIR__ . '/setup.php',
    __DIR__ . '/deploy.php',
    __DIR__ . '/migrate.php',
    __DIR__ . '/_ops_employee_tenant_check.php',
    __DIR__ . '/_ops_fix_employee_index.php',
    __DIR__ . '/_ops_optimize_clear.php',
];

$results = [];

foreach ($files as $file) {
    $name = basename($file);
    if (!file_exists($file)) {
        $results[] = ['file' => $name, 'status' => 'skip', 'msg' => 'Already gone'];
    } elseif (unlink($file)) {
        $results[] = ['file' => $name, 'status' => 'ok', 'msg' => 'Deleted'];
    } else {
        $results[] = ['file' => $name, 'status' => 'error', 'msg' => 'Failed — delete manually via File Manager'];
    }
}

// Delete self last
$results[] = ['file' => 'cleanup.php', 'status' => 'ok', 'msg' => 'Deleted'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cleanup</title>
<style>
  body { font-family: monospace; background: #0f1117; color: #d1d5db; padding: 32px; }
  h2   { color: #f9fafb; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; padding: 8px 12px; background: #1f2937; color: #9ca3af; font-size: .8rem; text-transform: uppercase; }
  td { padding: 8px 12px; border-bottom: 1px solid #1f2937; font-size: .85rem; }
  .ok    { color: #4ade80; font-weight: bold; }
  .skip  { color: #60a5fa; font-weight: bold; }
  .error { color: #f87171; font-weight: bold; }
  .banner { margin-top: 24px; padding: 12px 16px; border-radius: 6px; font-size: .9rem; }
  .success { background: #052e16; border: 1px solid #166534; color: #86efac; }
  .warn    { background: #431407; border: 1px solid #9a3412; color: #fed7aa; }
</style>
</head>
<body>
<h2>🧹 Server Cleanup</h2>
<table>
  <thead><tr><th>File</th><th>Status</th><th>Message</th></tr></thead>
  <tbody>
  <?php foreach ($results as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['file']) ?></td>
    <td class="<?= $r['status'] ?>"><?= strtoupper($r['status']) ?></td>
    <td><?= htmlspecialchars($r['msg']) ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php
$hasError = count(array_filter($results, fn($r) => $r['status'] === 'error')) > 0;
if ($hasError): ?>
<div class="banner warn">Some files could not be deleted. Remove them manually via Hostinger File Manager.</div>
<?php else: ?>
<div class="banner success">✅ All sensitive files removed. Server is clean.</div>
<?php endif; ?>

<?php @unlink(__FILE__); ?>
</body>
</html>
