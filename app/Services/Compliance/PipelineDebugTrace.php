<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\Log;

/**
 * RUNTIME DEBUG TRACE - Compliance Pipeline
 * 
 * This file instruments the entire preview → PDF pipeline
 * to identify where data is lost or malformed.
 */

class PipelineDebugTrace
{
    /**
     * Trace API response structure
     */
    public static function traceApiResponse(string $formCode, array $rawData): void
    {
        $trace = [
            'form_code' => $formCode,
            'api_records_count' => count($rawData['records'] ?? []),
            'api_records_empty' => empty($rawData['records']),
            'has_tenant' => isset($rawData['tenant']),
            'has_branch' => isset($rawData['branch']),
            'has_meta' => isset($rawData['meta']),
            'tenant_keys' => $rawData['tenant'] ? array_keys($rawData['tenant']) : [],
            'branch_keys' => $rawData['branch'] ? array_keys($rawData['branch']) : [],
            'meta_keys' => $rawData['meta'] ? array_keys($rawData['meta']) : [],
        ];

        if (empty($rawData['records'])) {
            Log::warning("TRACE: {$formCode} - API returned empty records", $trace);
        } else {
            Log::debug("TRACE: {$formCode} - API Response OK", $trace);
        }
    }

    /**
     * Trace generator output structure
     */
    public static function traceGeneratorOutput(string $formCode, array $formData): void
    {
        $trace = [
            'form_code' => $formCode,
            'generator_rows_count' => count($formData['rows'] ?? []),
            'generator_rows_empty' => empty($formData['rows']),
            'header_keys' => $formData['header'] ? array_keys($formData['header']) : [],
            'has_totals' => isset($formData['totals']),
            'is_nil' => $formData['is_nil'] ?? false,
        ];

        // Check for required header fields
        $requiredFields = [
            'form_title', 'tenant_name', 'factory_name', 'establishment_name',
            'place', 'district', 'address', 'branch_name', 'owner_name'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($formData['header'][$field])) {
                $missingFields[] = $field;
            }
        }

        $trace['missing_header_fields'] = $missingFields;

        if (!empty($missingFields)) {
            Log::warning("TRACE: {$formCode} - Missing header fields", $trace);
        } else {
            Log::debug("TRACE: {$formCode} - Generator Output OK", $trace);
        }
    }

    /**
     * Trace template variable passing
     */
    public static function traceTemplateVariables(string $formCode, string $template, array $viewData): void
    {
        $trace = [
            'form_code' => $formCode,
            'template' => $template,
            'view_data_keys' => array_keys($viewData),
            'rows_count' => count($viewData['rows'] ?? []),
            'header_keys' => isset($viewData['header']) ? array_keys($viewData['header']) : [],
        ];

        Log::debug("TRACE: {$formCode} - Template Variables", $trace);
    }

    /**
     * Trace batch form processing
     */
    public static function traceBatchFormProcessing(string $formCode, int $batchId, array $result): void
    {
        $trace = [
            'form_code' => $formCode,
            'batch_id' => $batchId,
            'status' => $result['status'] ?? 'unknown',
            'error' => $result['error'] ?? null,
            'records_generated' => $result['records_generated'] ?? 0,
        ];

        if ($result['status'] === 'failed') {
            Log::error("TRACE: {$formCode} - Batch Processing Failed", $trace);
        } else {
            Log::debug("TRACE: {$formCode} - Batch Processing OK", $trace);
        }
    }
}
