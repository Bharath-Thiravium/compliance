<?php

namespace App\Services\Compliance\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;

class CompliancePdfService
{
    /**
     * Generate PDF from pre-rendered HTML.
     * HTML must be the exact same output used for preview rendering.
     */
    public function generatePdf(string $html, string $orientation = 'portrait', string $paper = 'A4'): string
    {
        try {
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
            logger()->error("PDF generation failed: " . $e->getMessage());
            throw $e;
        }
    }
}
