<?php

namespace App\Services\Compliance\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;

class CompliancePdfService
{
    public function generatePdf(string $html): string
    {
        try {
            $pdf = Pdf::loadHTML($html)
                ->setPaper('A4', 'portrait')
                ->setOption('isHtml5ParserEnabled', false)
                ->setOption('isRemoteEnabled', false)
                ->setOption('dpi', 72)
                ->setOption('defaultFont', 'DejaVu Sans')
                ->setOption('chroot', [public_path()]);

            return $pdf->output();
        } catch (\Exception $e) {
            logger()->error("PDF generation failed: " . $e->getMessage());
            throw $e;
        }
    }
}
