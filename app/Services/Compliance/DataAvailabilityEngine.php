<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataAvailabilityEngine
{
    /**
     * Check data availability for a batch
     */
    public function checkDataAvailability(
        int $tenantId,
        int $branchId,
        int $month,
        int $year
    ): array {
        $missing = [];
        $summary = [];

        // Check employees
        $result = $this->checkTable('workforce_employee', $tenantId, $branchId);
        if (!$result['exists']) $missing[] = 'employees';
        $summary['employees'] = $result['count'];

        // Check attendance
        $result = $this->checkTableByPeriod('workforce_attendance', $tenantId, $branchId, $month, $year, 'attendance_date');
        if (!$result['exists']) $missing[] = 'attendance';
        $summary['attendance_records'] = $result['count'];

        // Check payroll - use created_at as fallback
        $result = $this->checkPayrollData($tenantId, $branchId, $month, $year);
        if (!$result['exists']) $missing[] = 'payroll';
        $summary['payroll_entries'] = $result['count'];

        // Check contract labour
        $result = $this->checkTable('contract_labour_deployment', $tenantId, $branchId);
        if (!$result['exists']) $missing[] = 'contract_labour';
        $summary['contract_labour'] = $result['count'];

        // Check bonus records - no period filter needed
        $result = $this->checkBonusData($tenantId, $branchId);
        if (!$result['exists']) $missing[] = 'bonus_records';
        $summary['bonus_records'] = $result['count'];

        // Check incidents - use notice_date
        $result = $this->checkTableByPeriod('incidents', $tenantId, $branchId, $month, $year, 'notice_date');
        if (!$result['exists']) $missing[] = 'incidents';
        $summary['incidents'] = $result['count'];

        // Check hazard register
        $result = $this->checkTable('hazard_register', $tenantId, $branchId);
        if (!$result['exists']) $missing[] = 'hazard_register';
        $summary['hazard_register'] = $result['count'];

        return [
            'all_data_exists' => empty($missing),
            'missing_data' => $missing,
            'data_summary' => $summary,
        ];
    }

    /**
     * Check if table exists and has data
     */
    private function checkTable(string $table, int $tenantId, int $branchId): array
    {
        try {
            if (!Schema::hasTable($table)) {
                return ['exists' => false, 'count' => 0];
            }

            $count = DB::table($table)
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->count();

            return ['exists' => $count > 0, 'count' => $count];
        } catch (\Exception $e) {
            \Log::warning("Error checking table {$table}: " . $e->getMessage());
            return ['exists' => false, 'count' => 0];
        }
    }

    /**
     * Check if table exists and has data (without branch filter)
     */
    private function checkTableWithoutBranch(string $table, int $tenantId): array
    {
        try {
            if (!Schema::hasTable($table)) {
                return ['exists' => false, 'count' => 0];
            }

            $count = DB::table($table)
                ->where('tenant_id', $tenantId)
                ->count();

            return ['exists' => $count > 0, 'count' => $count];
        } catch (\Exception $e) {
            \Log::warning("Error checking table {$table}: " . $e->getMessage());
            return ['exists' => false, 'count' => 0];
        }
    }

    /**
     * Check if table exists and has data for period
     */
    private function checkTableByPeriod(
        string $table,
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        string $dateColumn
    ): array {
        try {
            if (!Schema::hasTable($table)) {
                return ['exists' => false, 'count' => 0];
            }

            $count = DB::table($table)
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->whereYear($dateColumn, $year)
                ->whereMonth($dateColumn, $month)
                ->count();

            return ['exists' => $count > 0, 'count' => $count];
        } catch (\Exception $e) {
            \Log::warning("Error checking table {$table} by period: " . $e->getMessage());
            return ['exists' => false, 'count' => 0];
        }
    }

    /**
     * Check payroll data with fallback to created_at and flexible date range
     */
    private function checkPayrollData(int $tenantId, int $branchId, int $month, int $year): array
    {
        try {
            if (!Schema::hasTable('workforce_payroll_entry')) {
                return ['exists' => false, 'count' => 0];
            }

            // Check if ANY payroll exists for this tenant/branch (flexible date range)
            $count = DB::table('workforce_payroll_entry')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->count();

            return ['exists' => $count > 0, 'count' => $count];
        } catch (\Exception $e) {
            \Log::warning("Error checking payroll data: " . $e->getMessage());
            return ['exists' => false, 'count' => 0];
        }
    }

    /**
     * Check bonus data
     */
    private function checkBonusData(int $tenantId, int $branchId): array
    {
        try {
            if (!Schema::hasTable('bonus_records')) {
                return ['exists' => false, 'count' => 0];
            }

            $count = DB::table('bonus_records')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->count();

            return ['exists' => $count > 0, 'count' => $count];
        } catch (\Exception $e) {
            \Log::warning("Error checking bonus data: " . $e->getMessage());
            return ['exists' => false, 'count' => 0];
        }
    }
}
