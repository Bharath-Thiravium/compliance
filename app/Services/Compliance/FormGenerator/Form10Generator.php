<?php

namespace App\Services\Compliance\FormGenerator;

class Form10Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_10';
    protected string $view = 'compliance.forms.form_10';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $record = $this->normalizeRecord($record);
            
            // Calculate derived fields
            $totalDaysWorked = $record['total_days_worked'] ?? 26;
            $basicEarned = $record['basic_earned'] ?? 0;
            $daEarned = $record['da_earned'] ?? 0;
            $hraEarned = $record['hra_earned'] ?? 0;
            $otherAllowances = $record['other_allowances'] ?? 0;
            $overtimeHours = $record['overtime_hours'] ?? 0;
            $overtimeWages = $record['overtime_wages'] ?? 0;
            
            // Normal earnings = basic + da + hra + other allowances
            $normalEarnings = $basicEarned + $daEarned + $hraEarned + $otherAllowances;
            
            // Calculate normal rate per hour (assuming 8 hour day, 26 working days)
            $normalRate = $totalDaysWorked > 0 ? ($normalEarnings / ($totalDaysWorked * 8)) : 0;
            
            // Calculate overtime rate (typically 1.5x normal rate)
            $overtimeRate = $overtimeHours > 0 ? ($overtimeWages / $overtimeHours) : 0;
            
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'employee_name' => $record['name'] ?? '',
                'designation' => $record['designation'] ?? '',
                'department' => $record['department'] ?? '',
                'normal_rate' => round($normalRate, 2),
                'overtime_rate' => round($overtimeRate, 2),
                'normal_earnings' => round($normalEarnings, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'overtime_wages' => round($overtimeWages, 2),
                'food_grain_benefit' => 0,
                'is_piece_worker'    => false,
                'piece_worker_overtime' => 0,
                'total'              => round($normalEarnings + $overtimeWages, 2),
            ];
        }

        $filteredRows = array_values(array_filter($rows, fn($r) => ($r['overtime_hours'] ?? 0) > 0));
        $hasOvertime = count($filteredRows) > 0;

        $totals = $hasOvertime ? $this->calculateTotals($filteredRows, [
            'normal_rate', 'overtime_rate', 'normal_earnings', 'overtime_hours',
            'overtime_wages', 'food_grain_benefit', 'piece_worker_overtime'
        ]) : [];

        return [
            'header' => [
                'form_title'         => 'FORM 10 - Overtime Muster Roll',
                'period'             => $rawData['period'] ?? $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? 2024),
                'total_workers'      => $rawData['meta']['total_workers'] ?? 0,
                'contractor_name'    => $rawData['contractor_name'] ?? '',
                'principal_employer' => $rawData['tenant']['name'] ?? '',
                'branch'             => $rawData['branch'] ?? [],
                'tenant'             => $rawData['tenant'] ?? [],
            ],
            'rows'        => $filteredRows,
            'totals'      => $totals,
            'hasOvertime' => $hasOvertime,
            'is_nil'      => !$hasOvertime,
        ];
    }
}
