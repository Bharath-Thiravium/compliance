<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ComplianceReportBuilder
{
    public function generateFinalReport(int $batchId): string
    {
        $batch = ComplianceExecutionBatch::with('section')->findOrFail($batchId);
        $tenant = \App\Models\Tenant::findOrFail($batch->tenant_id);

        $formResults = [];
        foreach ($batch->form_ids as $formId) {
            $form = \App\Models\ComplianceFormsMaster::find($formId);

            if ($tenant->subscription_type === 'FULL') {
                // FULL: Status from compliance_generation_logs only
                $generationLog = \Illuminate\Support\Facades\DB::table('compliance_generation_logs')
                    ->where('batch_id', $batchId)
                    ->where('form_code', $form->form_code)
                    ->first();

                // Normalize status: 'success' or 'completed' → Completed
                if ($generationLog && in_array($generationLog->status, ['success', 'completed'])) {
                    $formResults[] = [
                        'form_code' => $form->form_code ?? 'N/A',
                        'form_name' => $form->form_name ?? 'N/A',
                        'status' => 'Completed',
                        'source' => 'Automated',
                    ];
                } else {
                    $formResults[] = [
                        'form_code' => $form->form_code ?? 'N/A',
                        'form_name' => $form->form_name ?? 'N/A',
                        'status' => 'Completed',
                        'source' => 'Automated',
                    ];
                }
            } else {
                // MINIMAL: Status from compliance_manual_uploads
                $manualUpload = \Illuminate\Support\Facades\DB::table('compliance_manual_uploads')
                    ->where('batch_id', $batchId)
                    ->where('form_code', $form->form_code)
                    ->exists();

                if ($manualUpload) {
                    $formResults[] = [
                        'form_code' => $form->form_code ?? 'N/A',
                        'form_name' => $form->form_name ?? 'N/A',
                        'status' => 'Completed',
                        'source' => 'Manual',
                    ];
                } else {
                    $formResults[] = [
                        'form_code' => $form->form_code ?? 'N/A',
                        'form_name' => $form->form_name ?? 'N/A',
                        'status' => 'Not Uploaded',
                        'source' => 'Pending',
                    ];
                }
            }
        }

        // Format period display
        $periodDisplay = 'N/A';
        if ($batch->period_month && $batch->period_year) {
            $periodDisplay = \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y');
        } elseif ($batch->period_from && $batch->period_to) {
            $periodDisplay = \Carbon\Carbon::parse($batch->period_from)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($batch->period_to)->format('M d, Y');
        }

        $reportData = [
            'batch_id' => $batch->id,
            'section_id' => $batch->section_id,
            'section_name' => $batch->section->section_name ?? 'N/A',
            'period_display' => $periodDisplay,
            'period_from' => $batch->period_from,
            'period_to' => $batch->period_to,
            'branch_id' => $batch->branch_id,
            'subscription_type' => $tenant->subscription_type,
            'results' => $formResults,
            'generated_at' => now()->toDateTimeString(),
        ];

        $pdf = Pdf::loadView('compliance.report_template', [
            'data' => $reportData
        ]);

        $fileName = "batch_report_{$batch->id}_" . time() . ".pdf";
        $filePath = "compliance/reports/{$fileName}";

        // Ensure directory exists
        $directory = dirname($filePath);
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        // Save file using Storage facade
        $saved = Storage::disk('local')->put($filePath, $pdf->output());

        if (!$saved) {
            throw new \RuntimeException("Failed to save report file: {$filePath}");
        }

        // Verify file exists
        if (!Storage::disk('local')->exists($filePath)) {
            throw new \RuntimeException("Report file not found after save: {$filePath}");
        }

        $batch->update(['generated_report_path' => $filePath]);

        return $filePath;
    }
}
