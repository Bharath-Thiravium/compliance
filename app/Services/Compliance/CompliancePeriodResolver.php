<?php

namespace App\Services\Compliance;

use Carbon\Carbon;

class CompliancePeriodResolver
{
    /**
     * Create compliance period from month and year
     */
    public static function create(int $month, int $year): array
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Invalid month: {$month}. Must be between 1 and 12.");
        }
        if ($year < 2000 || $year > 2100) {
            throw new \InvalidArgumentException("Invalid year: {$year}. Must be between 2000 and 2100.");
        }

        $compliancePeriod = sprintf('%04d-%02d', $year, $month);
        $carbon = Carbon::create($year, $month, 1);

        return [
            'month' => $month,
            'year' => $year,
            'compliance_period' => $compliancePeriod,
            'period_from' => $carbon->toDateString(),
            'period_to' => $carbon->endOfMonth()->toDateString(),
            'display_text' => $carbon->format('F Y'),
        ];
    }

    /**
     * Get current month/year
     */
    public static function getCurrentMonth(): array
    {
        $now = now();
        return self::create($now->month, $now->year);
    }

    /**
     * Format compliance period for display
     */
    public static function formatDisplay(string $compliancePeriod): string
    {
        try {
            return Carbon::createFromFormat('Y-m', $compliancePeriod)->format('F Y');
        } catch (\Throwable) {
            return $compliancePeriod;
        }
    }

    /**
     * Extract month/year from compliance period string
     */
    public static function extract(string $compliancePeriod): array
    {
        [$year, $month] = explode('-', $compliancePeriod);
        return [
            'year' => (int)$year,
            'month' => (int)$month,
        ];
    }

    /**
     * Filter query by compliance period
     */
    public static function filterByPeriod(
        $query,
        string $compliancePeriod,
        string $dateColumn = 'created_at'
    ) {
        return $query->whereRaw(
            "DATE_FORMAT({$dateColumn}, '%Y-%m') = ?",
            [$compliancePeriod]
        );
    }

    /**
     * Check if dataset exists for compliance period
     */
    public static function datasetExists(
        int $tenantId,
        int $branchId,
        string $compliancePeriod
    ): bool {
        $attendanceCount = \DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$compliancePeriod])
            ->count();

        return $attendanceCount > 0;
    }

    /**
     * Get available periods with data
     */
    public static function getAvailablePeriods(int $tenantId, int $branchId): array
    {
        $periods = \DB::table('workforce_attendance')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->selectRaw("DATE_FORMAT(attendance_date, '%Y-%m') as period, COUNT(*) as records")
            ->groupByRaw("DATE_FORMAT(attendance_date, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(attendance_date, '%Y-%m') DESC")
            ->get()
            ->map(fn($row) => [
                'compliance_period' => $row->period,
                'display_text' => Carbon::createFromFormat('Y-m', $row->period)->format('F Y'),
                'records' => $row->records,
            ])
            ->toArray();

        return $periods;
    }
}
