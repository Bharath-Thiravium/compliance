<?php

namespace App\Services\Compliance\Validation;

class LayoutIntegrityValidator
{
    public function validate(string $formCode, array $preparedData): array
    {
        $violations = [];
        $config = config("tn_statutory_rules.{$formCode}");

        if (!$config) {
            return $violations;
        }

        // Column alignment preserved
        if (!empty($config['column_sequence']) && !empty($preparedData['rows'])) {
            $expectedColumns = $config['column_sequence'];
            
            foreach ($preparedData['rows'] as $index => $row) {
                $actualColumns = array_keys($row);
                
                if ($actualColumns !== $expectedColumns) {
                    $violations[] = [
                        'type' => 'layout',
                        'field' => "rows[{$index}]",
                        'message' => 'Column alignment not preserved in row ' . ($index + 1)
                    ];
                    break; // Report once
                }
            }
        }

        // No missing headers
        if (!empty($config['mandatory_headers'])) {
            foreach ($config['mandatory_headers'] as $header) {
                if (!isset($preparedData[$header])) {
                    $violations[] = [
                        'type' => 'layout',
                        'field' => $header,
                        'message' => "Missing header: {$header}"
                    ];
                }
            }
        }

        // No extra columns
        if (!empty($config['column_sequence']) && !empty($preparedData['rows'])) {
            $allowedColumns = $config['column_sequence'];
            $actualColumns = array_keys($preparedData['rows'][0] ?? []);
            $extraColumns = array_diff($actualColumns, $allowedColumns);
            
            if (!empty($extraColumns)) {
                $violations[] = [
                    'type' => 'layout',
                    'field' => 'columns',
                    'message' => 'Extra columns found: ' . implode(', ', $extraColumns)
                ];
            }
        }

        // TN statutory sequence preserved
        if (!empty($config['tn_sequence_order'])) {
            $actualOrder = array_keys($preparedData);
            $expectedOrder = $config['tn_sequence_order'];
            
            $relevantActual = array_intersect($actualOrder, $expectedOrder);
            $relevantExpected = array_intersect($expectedOrder, $actualOrder);
            
            if (array_values($relevantActual) !== array_values($relevantExpected)) {
                $violations[] = [
                    'type' => 'layout',
                    'field' => 'sequence',
                    'message' => 'TN statutory sequence not preserved'
                ];
            }
        }

        // Register format matches TN government notification layout
        if (!empty($config['layout_template'])) {
            $requiredSections = $config['layout_template'];
            
            foreach ($requiredSections as $section) {
                if (!isset($preparedData[$section])) {
                    $violations[] = [
                        'type' => 'layout',
                        'field' => $section,
                        'message' => "Layout section missing: {$section} (required by TN notification)"
                    ];
                }
            }
        }

        return $violations;
    }
}
