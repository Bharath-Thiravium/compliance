<?php

namespace App\Services\Compliance\Validation;

class ComplianceFormValidator
{
    public function validate(string $formCode, array $data): array
    {
        $violations = [];
        $warnings = [];

        // Check required keys
        if (!isset($data['header'])) {
            $violations[] = [
                'type' => 'structural',
                'field' => 'header',
                'message' => 'Header information missing'
            ];
        }

        if (!isset($data['rows']) && !isset($data['entries'])) {
            $warnings[] = [
                'type' => 'structural',
                'field' => 'rows',
                'message' => 'Rows dataset missing'
            ];
        }

        // Validate types if present
        if (isset($data['rows']) && !is_array($data['rows'])) {
            $violations[] = [
                'type' => 'structural',
                'field' => 'rows',
                'message' => 'Rows must be array'
            ];
        }

        if (isset($data['entries']) && !is_array($data['entries'])) {
            $violations[] = [
                'type' => 'structural',
                'field' => 'entries',
                'message' => 'Entries must be array'
            ];
        }

        if (isset($data['totals']) && !is_array($data['totals'])) {
            $violations[] = [
                'type' => 'structural',
                'field' => 'totals',
                'message' => 'Totals must be array or empty'
            ];
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
            'warnings' => $warnings
        ];
    }
}
