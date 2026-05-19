<?php

namespace App\Services\Compliance\Validation;

class StructuralFormatValidator
{
    public function validate(string $formCode, array $preparedData): array
    {
        $violations = [];
        $config = config("tn_statutory_rules.{$formCode}");

        if (!$config) {
            return [['type' => 'structural', 'field' => 'config', 'message' => "No TN statutory config for {$formCode}"]];
        }

        // Blade Structure Validation instead of raw DB structural validation
        $requiredBladeStructures = [
            'form_title',
            'form_code',
            'header',
            'period_month',
            'period_year'
        ];

        foreach ($requiredBladeStructures as $field) {
            if (!isset($preparedData[$field])) {
                $violations[] = ['type' => 'structural', 'field' => $field, 'message' => "Missing Blade required field: {$field}"];
            }
        }

        if (!isset($preparedData['is_nil']) || $preparedData['is_nil'] !== true) {
            if (!isset($preparedData['rows']) && !isset($preparedData['entries'])) {
                 $violations[] = ['type' => 'structural', 'field' => 'rows', 'message' => "Missing row array for populated form."];
            }
        }

        return $violations;
    }

    private function isValidDateFormat(string $date): bool
    {
        return (bool) preg_match('/^\d{2}-\d{2}-\d{4}$/', $date);
    }
}
