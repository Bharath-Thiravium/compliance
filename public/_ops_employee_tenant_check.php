<?php

/**
 * One-time shared-host diagnostic for tenant/user/employee-code scoping.
 *
 * Visit:
 * /public/_ops_employee_tenant_check.php?token=ops_compliance_2026&email=lepl@123.com&employee_code=MAK001
 *
 * Delete this file after use.
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

header('Content-Type: application/json; charset=UTF-8');

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$provided = isset($_GET['token']) ? (string) $_GET['token'] : '';
if (! hash_equals('ops_compliance_2026', $provided)) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'error' => 'Forbidden. Use ?token=ops_compliance_2026',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$email = isset($_GET['email']) ? (string) $_GET['email'] : '';
$code = isset($_GET['employee_code']) ? (string) $_GET['employee_code'] : 'MAK001';

try {
    $user = $email !== ''
        ? DB::table('users')
            ->select('id', 'email', 'tenant_id', 'branch_id')
            ->where('email', $email)
            ->first()
        : null;

    $employees = DB::table('workforce_employee')
        ->select('id', 'tenant_id', 'branch_id', 'employee_code', 'name', 'gender')
        ->where('employee_code', $code)
        ->orderBy('tenant_id')
        ->orderBy('id')
        ->get();

    $indexes = collect(DB::select('SHOW INDEX FROM `workforce_employee`'))
        ->map(fn ($row) => [
            'key_name' => $row->Key_name ?? null,
            'column' => $row->Column_name ?? null,
            'non_unique' => $row->Non_unique ?? null,
            'seq' => $row->Seq_in_index ?? null,
        ])
        ->values();

    echo json_encode([
        'ok' => true,
        'checked_user' => $user,
        'employee_code' => $code,
        'matching_employees' => $employees,
        'workforce_employee_indexes' => $indexes,
        'expected_index' => 'workforce_employee_tenant_employee_code_unique on tenant_id + employee_code',
        'note' => 'If checked_user.tenant_id matches a matching employee tenant_id, upload updates that row. If not, upload should create a separate row for this tenant.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
