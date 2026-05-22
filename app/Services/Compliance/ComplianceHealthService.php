<?php

namespace App\Services\Compliance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComplianceHealthService
{
    public function calculateScore(int $tenantId, int $month, int $year): array
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $scores = [
            'payroll_available' => $this->checkPayrollAvailable($tenantId, $periodStart, $periodEnd),
            'payroll_locked' => $this->checkPayrollLocked($tenantId, $periodStart, $periodEnd),
            'forms_generated' => $this->checkFormsGenerated($tenantId, $month, $year),
            'no_errors' => $this->checkNoErrors($tenantId, $month, $year),
            'timeline_compliance' => $this->checkTimelineCompliance($tenantId, $month, $year),
        ];

        $totalScore = array_sum($scores);
        $status = $totalScore >= 85 ? 'Excellent' : ($totalScore >= 60 ? 'Good' : 'Risk');

        return [
            'percentage' => round($totalScore, 1),
            'status' => $status,
            'breakdown' => [
                'Payroll Available' => $scores['payroll_available'],
                'Payroll Locked' => $scores['payroll_locked'],
                'Forms Generated' => $scores['forms_generated'],
                'No Generation Errors' => $scores['no_errors'],
                'Timeline Compliance' => $scores['timeline_compliance'],
            ],
        ];
    }

    private function checkPayrollAvailable(int $tenantId, Carbon $start, Carbon $end): float
    {
        $count = DB::table('workforce_payroll_entry')
            ->join('workforce_payroll_cycle', 'workforce_payroll_entry.payroll_cycle_id', '=', 'workforce_payroll_cycle.id')
            ->where('workforce_payroll_entry.tenant_id', $tenantId)
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('workforce_payroll_cycle.period_from', [$start, $end])
                      ->orWhereBetween('workforce_payroll_cycle.period_to', [$start, $end]);
            })
            ->count();

        return $count > 0 ? 20 : 0;
    }

    private function checkPayrollLocked(int $tenantId, Carbon $start, Carbon $end): float
    {
        if (!DB::getSchemaBuilder()->hasTable('payroll_locks')) {
            return 20;
        }

        $locked = DB::table('payroll_locks')
            ->where('tenant_id', $tenantId)
            ->whereBetween('period_date', [$start, $end])
            ->where('is_locked', true)
            ->exists();

        return $locked ? 20 : 10;
    }

    private function checkFormsGenerated(int $tenantId, int $month, int $year): float
    {
        $batches = DB::table('compliance_execution_batches')
            ->where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->count();

        return $batches > 0 ? 20 : 0;
    }

    private function checkNoErrors(int $tenantId, int $month, int $year): float
    {
        $batchIds = DB::table('compliance_execution_batches')
            ->where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->pluck('id');

        if ($batchIds->isEmpty()) {
            return 0;
        }

        $errors = DB::table('compliance_generation_logs')
            ->whereIn('batch_id', $batchIds)
            ->where('status', 'failed')
            ->count();

        return $errors === 0 ? 20 : 10;
    }

    private function checkTimelineCompliance(int $tenantId, int $month, int $year): float
    {
        $total = DB::table('compliance_timelines')
            ->where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $compliant = DB::table('compliance_timelines')
            ->where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereIn('status', ['Generated', 'Filed'])
            ->count();

        return round(($compliant / $total) * 20, 1);
    }
}
