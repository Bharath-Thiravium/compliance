<?php

namespace App\Services\Compliance\Validation;

class LegalRuleValidator
{
    public function validate(string $formCode, array $preparedData): array
    {
        $violations = [];
        $config = config("tn_statutory_rules.{$formCode}");

        if (!$config || empty($preparedData['rows'])) {
            return $violations;
        }

        foreach ($preparedData['rows'] as $index => $row) {
            // Required statutory fields
            if (!empty($config['required_row_fields'])) {
                foreach ($config['required_row_fields'] as $field) {
                    if (!isset($row[$field]) || $row[$field] === '' || $row[$field] === null) {
                        $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].{$field}", 'message' => "Required statutory field missing: {$field}"];
                    }
                }
            }

            // Minimum wage compliance
            if (!empty($config['min_wage']) && isset($row['wages']) && $row['wages'] > 0) {
                if ($row['wages'] < $config['min_wage']) {
                    $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].wages", 'message' => "Wages below TN minimum wage: ₹{$config['min_wage']}"];
                }
            }

            // Overtime calculation correctness
            if (!empty($config['overtime_multiplier']) && isset($row['overtime_hours']) && $row['overtime_hours'] > 0) {
                $expectedRate = ($row['basic_wage'] ?? 0) * $config['overtime_multiplier'];
                if (isset($row['overtime_rate']) && $row['overtime_rate'] < $expectedRate) {
                    $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].overtime_rate", 'message' => "Overtime rate must be >= {$config['overtime_multiplier']}x basic wage"];
                }
            }

            // ESI contribution calculation
            if (!empty($config['esi_rate']) && isset($row['esi_contribution'])) {
                $expectedESI = round(($row['gross_wages'] ?? 0) * $config['esi_rate'] / 100, 2);
                if (abs($row['esi_contribution'] - $expectedESI) > 1) {
                    $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].esi_contribution", 'message' => "ESI contribution calculation incorrect (expected: ₹{$expectedESI})"];
                }
            }

            // EPF validation
            if (!empty($config['epf_rate']) && isset($row['epf_contribution'])) {
                $expectedEPF = round(($row['basic_wage'] ?? 0) * $config['epf_rate'] / 100, 2);
                if (abs($row['epf_contribution'] - $expectedEPF) > 1) {
                    $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].epf_contribution", 'message' => "EPF contribution calculation incorrect (expected: ₹{$expectedEPF})"];
                }
            }

            // Child labour prohibition
            if (!empty($config['check_child_labour']) && isset($row['age']) && $row['age'] < 14) {
                $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].age", 'message' => 'Child labour prohibited: Age below 14 years', 'severity' => 'critical'];
            }

            // Gender-based register compliance
            if (!empty($config['gender_required']) && empty($row['gender'])) {
                $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].gender", 'message' => 'Gender field required for this register'];
            }

            // Statutory threshold applicability
            if (!empty($config['wage_threshold']) && isset($row['wages'])) {
                if ($row['wages'] > $config['wage_threshold'] && empty($row['threshold_compliance_flag'])) {
                    $violations[] = ['type' => 'legal', 'field' => "rows[{$index}].threshold_compliance_flag", 'message' => "Wage exceeds threshold ₹{$config['wage_threshold']}, compliance flag required"];
                }
            }
        }

        // NIL handling compliance
        if (empty($preparedData['rows']) && empty($preparedData['is_nil'])) {
            $violations[] = ['type' => 'legal', 'field' => 'is_nil', 'message' => 'No data present but NIL declaration missing'];
        }

        return $violations;
    }
}
