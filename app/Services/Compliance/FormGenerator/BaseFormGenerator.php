<?php

namespace App\Services\Compliance\FormGenerator;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

/**
 * BaseFormGenerator - Data Transformation Layer
 *
 * Responsibility: Transform API data into form structure
 * Does NOT: Query database, validate data, or orchestrate execution
 *
 * Pipeline: API Service → Generator → Blade Template
 */
abstract class BaseFormGenerator
{
    protected string $formCode;
    protected string $view;
    protected array $config;

    public function __construct()
    {
        $this->config = config("compliance_forms.{$this->formCode}", []);
    }

    /**
     * Public interface for data transformation
     * Transforms API data into form structure
     *
     * @param array $rawData Data from API service
     * @return array Formatted data: ['header' => [...], 'rows' => [...], 'totals' => [...], 'is_nil' => bool]
     */
    final public function generate(array $rawData): array
    {
        if (isset($rawData['records'])) {
            $rawData['records'] = $this->normalizeRecords($rawData['records']);
        }

        return $this->prepareData($rawData);
    }

    /**
     * Transform API data into form structure (implementation)
     *
     * @param array $rawData Data from API service
     * @return array Formatted data: ['header' => [...], 'rows' => [...], 'totals' => [...], 'is_nil' => bool]
     */
    abstract protected function prepareData(array $rawData): array;

    /**
     * Generate PDF from pre-rendered HTML (same HTML used for preview).
     * Accepts the rendered HTML string so PDF matches preview exactly.
     *
     * @param array $formData Formatted data from generate() — must contain 'rendered_html'
     * @return string PDF binary content
     */
    public function generatePdf(array $formData): string
    {
        try {
            $html = $formData['rendered_html'] ?? null;

            if (!$html) {
                // Fallback: render the view now (should not normally happen)
                $html = \Illuminate\Support\Facades\View::make($this->view, $formData)->render();
            }

            $formKey   = strtolower(str_replace('_', '', $this->formCode));
            $pdfConfig = config("pdf_{$formKey}.{$this->formCode}", []);
            $landscapeForms = ['FORM_2', 'FORM_10', 'FORM_11', 'FORM_12', 'FORM_17', 'FORM_18', 'FORM_25', 'ESI_FORM_11', 'FORM_XIII', 'FORM_XVII'];
            $orientation = $pdfConfig['orientation'] ?? (in_array($this->formCode, $landscapeForms) ? 'landscape' : 'portrait');
            $paper       = $pdfConfig['paper'] ?? 'A4';

            $pdf = Pdf::loadHTML($html)
                ->setPaper($paper, $orientation)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false)
                ->setOption('dpi', 96)
                ->setOption('defaultFont', 'Arial')
                ->setOption('isFontSubsettingEnabled', true)
                ->setOption('enable_php', false)
                ->setOption('chroot', [public_path()]);

            return $pdf->output();
        } catch (\Exception $e) {
            Log::error("PDF generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format period for display
     */
    protected function formatPeriod(int $month, int $year): string
    {
        return \Carbon\Carbon::create($year, $month, 1)->format('F Y');
    }

    /**
     * Calculate totals from rows
     */
    protected function calculateTotals(array $rows, array $fields): array
    {
        $totals = [];
        foreach ($fields as $field) {
            $totals[$field] = array_sum(array_column($rows, $field));
        }
        return $totals;
    }
    /**
     * Normalize records from API service
     * Converts stdClass objects to arrays, preserves arrays unchanged
     *
     * @param array $records Records from API service (may contain stdClass objects)
     * @return array Normalized records as arrays
     */
    protected function normalizeRecords($records): array
    {
        if (!is_array($records)) {
            Log::warning("Compliance record normalization issue", [
                'form_code' => $this->formCode,
                'issue' => 'records is not an array',
                'type' => gettype($records)
            ]);
            return [];
        }

        $normalized = [];
        foreach ($records as $record) {
            if (is_object($record)) {
                $normalized[] = (array) $record;
            } elseif (is_array($record)) {
                $normalized[] = $record;
            } else {
                Log::warning("Compliance record normalization issue", [
                    'form_code' => $this->formCode,
                    'issue' => 'invalid record type',
                    'type' => gettype($record)
                ]);
            }
        }

        return $normalized;
    }

    protected function normalizeRecord($record): array
    {
        return is_object($record) ? (array) $record : $record;
    }

    /**
     * Validate totals match calculated values
     */
    protected function validateTotals(array $data): void
    {
        if (isset($data['totals']) && isset($data['rows'])) {
            foreach ($data['totals'] as $field => $total) {
                $calculated = array_sum(array_column($data['rows'], $field));
                if (abs($calculated - $total) > 0.01) {
                    Log::error("Total mismatch for {$field} in {$this->formCode}", [
                        'expected' => $total,
                        'calculated' => $calculated
                    ]);
                }
            }
        }
    }
}
