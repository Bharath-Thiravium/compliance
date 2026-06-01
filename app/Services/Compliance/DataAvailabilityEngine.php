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
        $result = $this->checkTable('workforce_attendance', $tenantId, $branchId);
        if (!$result['exists']) $missing[] = 'attendance';
        $summary['attendance_records'] = $result['count'];

        // Check payroll - use created_at as fallback
        $result = $this->checkPayrollData($tenantId, $branchId, $month, $year);
        if (!$result['exists']) $missing[] = 'payroll';
        $summary['payroll_entries'] = $result['count'];

        // Optional data — shown in summary but do NOT block generation
        $result = $this->checkContractorData($tenantId, $branchId);
        $summary['contract_labour'] = $result['count'];

        $result = $this->checkBonusData($tenantId, $branchId);
        $summary['bonus_records'] = $result['count'];

        $result = $this->checkTable('incidents', $tenantId, $branchId);
        $summary['incidents'] = $result['count'];

        $result = $this->checkTable('hazard_register', $tenantId, $branchId);
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

            $q = DB::table($table)
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId);

            // Respect soft deletes where the column exists
            if (Schema::hasColumn($table, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }

            $count = $q->count();

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
     * Check contractor data (CSV uploads go to contractor_master)
     */
    private function checkContractorData(int $tenantId, int $branchId): array
    {
        try {
            $count = 0;
            if (Schema::hasTable('contractor_master')) {
                $count = DB::table('contractor_master')
                    ->where('tenant_id', $tenantId)
                    ->where('branch_id', $branchId)
                    ->count();
            }
            if ($count === 0 && Schema::hasTable('contractors')) {
                $count = DB::table('contractors')
                    ->where('tenant_id', $tenantId)
                    ->count();
            }
            return ['exists' => $count > 0, 'count' => $count];
        } catch (\Exception $e) {
            \Log::warning('Error checking contractor data: ' . $e->getMessage());
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
