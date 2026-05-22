<?php

namespace App\Services\Compliance;

class WageCalculationService
{
    public function calculateDailyRate(float $basicSalary): float
    {
        return $basicSalary > 0 ? round($basicSalary / 26, 2) : 0;
    }

    public function calculateBasicWages(float $dailyRate, int $daysWorked): float
    {
        return round($dailyRate * $daysWorked, 2);
    }

    public function calculateOvertimeWages(float $dailyRate, float $overtimeHours): float
    {
        if ($overtimeHours <= 0) {
            return 0;
        }
        $hourlyRate = $dailyRate / 8;
        return round($hourlyRate * 2 * $overtimeHours, 2);
    }

    public function prorateAllowance(float $fullMonthAmount, int $daysWorked, int $totalDays = 26): float
    {
        if ($daysWorked === 0) {
            return 0;
        }
        return round(($fullMonthAmount / $totalDays) * $daysWorked, 2);
    }

    public function validateWageConsistency(array $wageData): void
    {
        if ($wageData['days_worked'] === 0) {
            if ($wageData['basic_wages'] > 0 || $wageData['da'] > 0 || $wageData['hra'] > 0) {
                throw new \Exception("Wage components cannot exist when days_worked = 0");
            }
        }

        if ($wageData['overtime_hours'] === 0 && $wageData['overtime_wages'] > 0) {
            throw new \Exception("Overtime wages cannot exist when overtime_hours = 0");
        }
    }
}
