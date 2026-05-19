<?php

namespace App\Services\Compliance\Validation;

class ComputationValidator
{
    public function validate(string $formCode, array $preparedData): array
    {
        $violations = [];

        if (empty($preparedData['rows'])) {
            return $violations;
        }

        foreach ($preparedData['rows'] as $index => $row) {
            // Wage = Basic + DA + Allowances
            if (isset($row['basic_wage'], $row['da'], $row['allowances'], $row['gross_wages'])) {
                $computed = $row['basic_wage'] + $row['da'] + $row['allowances'];
                if (abs($row['gross_wages'] - $computed) > 1) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].gross_wages",
                        'message' => "Gross wages incorrect. Expected: ₹{$computed}, Found: ₹{$row['gross_wages']}"
                    ];
                }
            }

            // Net Pay = Gross - Deductions
            if (isset($row['gross_wages'], $row['total_deductions'], $row['net_pay'])) {
                $computed = $row['gross_wages'] - $row['total_deductions'];
                if (abs($row['net_pay'] - $computed) > 1) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].net_pay",
                        'message' => "Net pay incorrect. Expected: ₹{$computed}, Found: ₹{$row['net_pay']}"
                    ];
                }
            }

            // Overtime rate >= 2x basic
            if (isset($row['overtime_rate'], $row['basic_wage']) && $row['overtime_rate'] > 0) {
                $minRate = $row['basic_wage'] * 2;
                if ($row['overtime_rate'] < $minRate) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].overtime_rate",
                        'message' => "Overtime rate must be >= 2x basic wage (₹{$minRate})"
                    ];
                }
            }

            // ESI contribution % correct
            if (isset($row['esi_contribution'], $row['gross_wages']) && $row['esi_contribution'] > 0) {
                $expectedRate = 0.75; // 0.75% employee contribution
                $computed = round($row['gross_wages'] * $expectedRate / 100, 2);
                if (abs($row['esi_contribution'] - $computed) > 1) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].esi_contribution",
                        'message' => "ESI contribution incorrect. Expected: ₹{$computed} (0.75%), Found: ₹{$row['esi_contribution']}"
                    ];
                }
            }

            // EPF % correct
            if (isset($row['epf_contribution'], $row['basic_wage']) && $row['epf_contribution'] > 0) {
                $expectedRate = 12; // 12% employee contribution
                $computed = round($row['basic_wage'] * $expectedRate / 100, 2);
                if (abs($row['epf_contribution'] - $computed) > 1) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].epf_contribution",
                        'message' => "EPF contribution incorrect. Expected: ₹{$computed} (12%), Found: ₹{$row['epf_contribution']}"
                    ];
                }
            }

            // Bonus calculation correct (if applicable)
            if (isset($row['bonus_amount'], $row['wages_for_bonus']) && $row['bonus_amount'] > 0) {
                $minBonus = $row['wages_for_bonus'] * 0.0833; // 8.33% minimum
                $maxBonus = 7000 * 0.20; // 20% of ₹7000 ceiling
                
                if ($row['bonus_amount'] < $minBonus) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].bonus_amount",
                        'message' => "Bonus below minimum 8.33%. Expected: ₹{$minBonus}"
                    ];
                }
                
                if ($row['bonus_amount'] > $maxBonus) {
                    $violations[] = [
                        'type' => 'computation',
                        'field' => "rows[{$index}].bonus_amount",
                        'message' => "Bonus exceeds maximum ceiling. Max: ₹{$maxBonus}"
                    ];
                }
            }
        }

        return $violations;
    }
}
