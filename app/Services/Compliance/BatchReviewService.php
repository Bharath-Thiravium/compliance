<?php

namespace App\Services\Compliance;

use App\Models\ComplianceExecutionBatch;
use App\Models\ComplianceBatchForm;
use Carbon\Carbon;

class BatchReviewService
{
    public function __construct(
        private DataAvailabilityEngine $dataAvailabilityEngine
    ) {}

    public function prepareReviewData(int $batchId): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);

        $batchForms = ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'pending')
            ->get();

        $forms = $batchForms->map(function ($form) {
            return [
                'form_code' => $form->form_code,
                'section' => $form->section ?? 'General',
                'status' => $form->status,
            ];
        })->toArray();

        $dataCheck = [
            'all_data_exists' => true,
            'missing_data' => [],
            'data_summary' => [],
        ];

        try {
            $dataCheck = $this->dataAvailabilityEngine->checkDataAvailability(
                $batch->tenant_id,
                $batch->branch_id,
                $batch->period_month,
                $batch->period_year
            );
        } catch (\Exception $e) {
            \Log::warning('Data availability check failed', ['error' => $e->getMessage()]);
        }

        return [
            'batch_id' => $batch->id,
            'period' => Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y'),
            'forms' => $forms,
            'data_availability' => [
                'all_data_exists' => $dataCheck['all_data_exists'] ?? true,
                'missing_data' => $dataCheck['missing_data'] ?? [],
                'data_summary' => $dataCheck['data_summary'] ?? [],
            ],
        ];
    }
}
