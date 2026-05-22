<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SessionDataGenerationService
{
    /**
     * Insert session CSV data into DB, generate all batch PDFs, then roll back.
     * PDFs are written to disk before rollback — nothing persists in the DB.
     */
    public function generateFromSession(ComplianceExecutionBatch $batch, array $sessionData): array
    {
        $tenantId = $batch->tenant_id;
        $branchId = $batch->branch_id;
        $month    = $batch->period_month;
        $year     = $batch->period_year;

        $results = ['successful' => 0, 'failed' => 0, 'errors' => []];

        DB::beginTransaction();

        try {
            // ── 1. Insert employees ───────────────────────────────────────────
            $employeeIdMap = [];
            foreach ($sessionData['employees'] ?? [] as $row) {
                $id = DB::table('workforce_employee')->insertGetId(array_merge($row, [
                    'tenant_id'  => $tenantId,
                    'branch_id'  => $branchId,
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $employeeIdMap[$row['employee_code']] = $id;
            }

            // ── 2. Insert payroll cycle + entries ─────────────────────────────
            if (!empty($sessionData['payroll'])) {
                $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
                $periodEnd   = $periodStart->copy()->endOfMonth();

                $cycleId = DB::table('workforce_payroll_cycle')->insertGetId([
                    'tenant_id'    => $tenantId,
                    'cycle_name'   => 'Session ' . $periodStart->format('M Y'),
                    'period_from'  => $periodStart->toDateString(),
                    'period_to'    => $periodEnd->toDateString(),
                    'status'       => 'processed',
                    'processed_at' => now(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                foreach ($sessionData['payroll'] as $row) {
                    $empId = $employeeIdMap[$row['employee_code']] ?? null;
                    if (!$empId) continue;
                    DB::table('workforce_payroll_entry')->insert(array_merge($row, [
                        'tenant_id'        => $tenantId,
                        'branch_id'        => $branchId,
                        'employee_id'      => $empId,
                        'payroll_cycle_id' => $cycleId,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]));
                }
            }

            // ── 3. Insert attendance ──────────────────────────────────────────
            foreach ($sessionData['attendance'] ?? [] as $row) {
                $empId = $employeeIdMap[$row['employee_code']] ?? null;
                if (!$empId) continue;
                DB::table('workforce_attendance')->insert(array_merge($row, [
                    'tenant_id'   => $tenantId,
                    'branch_id'   => $branchId,
                    'employee_id' => $empId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]));
            }

            // ── 4. Generate all forms (PDFs written to disk) ──────────────────
            $orchestrator = app(ComplianceOrchestrator::class);
            $forms = ComplianceBatchForm::where('batch_id', $batch->id)->get();

            foreach ($forms as $form) {
                try {
                    $form->update(['status' => 'processing']);
                    $result = $orchestrator->execute(
                        $tenantId, $branchId, $month, $year,
                        $form->form_code, 'batch', $batch->id
                    );
                    if ($result['status'] === 'success') {
                        $results['successful']++;
                    } else {
                        $form->update(['status' => 'failed']);
                        $results['failed']++;
                        $results['errors'][] = $form->form_code . ': ' . ($result['error'] ?? 'unknown');
                    }
                } catch (\Exception $e) {
                    $form->update(['status' => 'failed']);
                    $results['failed']++;
                    $results['errors'][] = $form->form_code . ': ' . $e->getMessage();
                    Log::error('Session generation error', ['form' => $form->form_code, 'error' => $e->getMessage()]);
                }
            }

        } finally {
            // ── 5. Always roll back workforce data — PDFs already on disk ─────
            DB::rollBack();
        }

        // Re-apply only the compliance_batch_forms status updates (outside transaction)
        // because rollback wiped them too — re-read from disk to confirm
        $this->reapplyFormStatuses($batch->id, $tenantId);

        return $results;
    }

    /**
     * After rollback, re-check which PDFs exist on disk and update form statuses.
     */
    private function reapplyFormStatuses(int $batchId, int $tenantId): void
    {
        $forms = ComplianceBatchForm::where('batch_id', $batchId)->get();

        foreach ($forms as $form) {
            $expectedPath = "generated_forms/{$tenantId}/{$batchId}/{$form->form_code}.pdf";
            $exists = \Illuminate\Support\Facades\Storage::disk('local')->exists($expectedPath);

            $form->update([
                'status'    => $exists ? 'generated' : 'failed',
                'file_path' => $exists ? $expectedPath : null,
                'updated_at'=> now(),
            ]);
        }

        // Update batch status
        $generated = ComplianceBatchForm::where('batch_id', $batchId)->where('status', 'generated')->count();
        $total     = ComplianceBatchForm::where('batch_id', $batchId)->count();

        \App\Models\ComplianceExecutionBatch::where('id', $batchId)->update([
            'status' => $generated === $total ? 'processed' : ($generated > 0 ? 'partial' : 'failed'),
        ]);
    }
}
