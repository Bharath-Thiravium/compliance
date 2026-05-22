<?php

namespace App\Services\Compliance\Audit;

use App\Models\ComplianceAuditLog;
use App\Models\ComplianceBatchForm;
use App\Models\ComplianceExecutionBatch;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;
use App\Services\Compliance\FormGenerator\FormDataAggregator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ComplianceCorrectionService
{
    public function __construct(
        private FormDataAggregator $aggregator,
        private ComplianceAuditService $auditService
    ) {}

    public function fixFormViolations(int $batchId, string $formCode): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        $auditLog = ComplianceAuditLog::where('batch_id', $batchId)
            ->where('form_code', $formCode)
            ->first();

        if (!$auditLog || empty($auditLog->violations)) {
            return ['status' => 'no_violations', 'message' => 'No violations found'];
        }

        $violations = $auditLog->violations;
        $missingFields = [];
        $autoFixedData = [];

        foreach ($violations as $violation) {
            $field = $violation['field'] ?? null;
            if (!$field) continue;

            $value = $this->autoFetchFieldValue($field, $batch, $formCode);
            
            if ($value !== null) {
                $autoFixedData[$field] = $value;
            } else {
                $missingFields[] = [
                    'field' => $field,
                    'message' => $violation['message'] ?? "Missing: {$field}",
                    'type' => $violation['type'] ?? 'unknown'
                ];
            }
        }

        if (!empty($missingFields)) {
            return [
                'status' => 'requires_input',
                'missing_fields' => $missingFields,
                'auto_fixed' => $autoFixedData
            ];
        }

        return $this->regenerateAndAudit($batch, $formCode, $autoFixedData);
    }

    public function fixWithUserInput(int $batchId, string $formCode, array $userInput): array
    {
        $batch = ComplianceExecutionBatch::findOrFail($batchId);
        return $this->regenerateAndAudit($batch, $formCode, $userInput);
    }

    private function autoFetchFieldValue(string $field, ComplianceExecutionBatch $batch, string $formCode)
    {
        $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
            $batch->tenant_id,
            $batch->branch_id
        );

        // Try tenant master data
        if (in_array($field, ['establishment_name', 'factory_license_no', 'pf_code', 'esi_code'])) {
            $tenant = DB::table('tenants')->where('id', $batch->tenant_id)->first();
            if ($tenant) {
                $mapping = [
                    'establishment_name' => $tenant->establishment_name ?? $tenant->name,
                    'factory_license_no' => $tenant->factory_license_no,
                    'pf_code' => $tenant->pf_code,
                    'esi_code' => $tenant->esi_code
                ];
                return $mapping[$field] ?? null;
            }
        }

        // Try branch details
        if (in_array($field, ['unit_name', 'branch_name', 'address', 'factory_license_number'])) {
            $branch = DB::table('branches')
                ->where('id', $branchId)
                ->where('tenant_id', $batch->tenant_id)
                ->first();
            if ($branch) {
                $mapping = [
                    'unit_name' => $branch->unit_name ?? $branch->branch_name,
                    'branch_name' => $branch->branch_name,
                    'address' => $branch->address,
                    'factory_license_number' => $branch->factory_license_number
                ];
                return $mapping[$field] ?? null;
            }
        }

        // Try period fields
        if ($field === 'period_month') return $batch->period_month;
        if ($field === 'period_year') return $batch->period_year;

        return null;
    }

    private function regenerateAndAudit(ComplianceExecutionBatch $batch, string $formCode, array $correctionData): array
    {
        try {
            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $batch->tenant_id,
                $batch->branch_id
            );

            $factory = app(FormGeneratorFactory::class);
            $generator = $factory::make($formCode);

            if (!$generator) {
                return ['status' => 'error', 'message' => 'Generator not found'];
            }

            // Get raw data
            $tenant = DB::table('tenants')->where('id', $batch->tenant_id)->first();
            $isMinimal = $tenant && $tenant->subscription_type === 'MINIMAL';

            if ($isMinimal) {
                $adapter = app(\App\Services\Compliance\ManualDataAdapter::class);
                $rawData = $adapter->adaptForFormGenerator(
                    $formCode,
                    $batch->tenant_id,
                    $branchId,
                    $batch->period_month,
                    $batch->period_year
                );
            } else {
                $rawData = $this->aggregator->aggregate(
                    $formCode,
                    $batch->tenant_id,
                    $branchId,
                    $batch->period_month,
                    $batch->period_year
                );
            }

            // Merge correction data
            $rawData = array_merge($rawData, $correctionData);

            // Prepare data
            $reflection = new \ReflectionClass($generator);
            $prepareMethod = $reflection->getMethod('prepareData');
            $prepareMethod->setAccessible(true);
            $preparedData = $prepareMethod->invoke($generator, $rawData);

            // Merge corrections into prepared data as well
            $preparedData = array_merge($preparedData, $correctionData);

            // Generate new PDF
            $pdfOutput = $generator->generate(
                $batch->tenant_id,
                $branchId,
                $batch->period_month,
                $batch->period_year,
                $batch->id
            );

            // Find existing batch form record
            $batchForm = ComplianceBatchForm::where('batch_id', $batch->id)
                ->where('form_code', $formCode)
                ->where('tenant_id', $batch->tenant_id)
                ->first();

            if (!$batchForm) {
                return ['status' => 'error', 'message' => 'Batch form record not found'];
            }

            // Delete old file
            if ($batchForm->file_path && Storage::disk('local')->exists($batchForm->file_path)) {
                Storage::disk('local')->delete($batchForm->file_path);
            }

            // Save new PDF with same path structure
            $filePath = "compliance/generated/{$batch->id}/{$formCode}.pdf";
            Storage::disk('local')->put($filePath, $pdfOutput);

            // Update batch form record
            $batchForm->update([
                'file_path' => $filePath,
                'status' => 'success'
            ]);

            // CRITICAL: Re-audit immediately
            $auditResult = $this->auditService->audit($formCode, $preparedData);

            // CRITICAL: Update audit log with new score
            ComplianceAuditLog::updateOrCreate(
                [
                    'tenant_id' => $batch->tenant_id,
                    'batch_id' => $batch->id,
                    'form_code' => $formCode,
                ],
                [
                    'audit_score' => $auditResult['score'],
                    'status' => $auditResult['status'],
                    'violations' => $auditResult['violations'],
                    'updated_at' => now(),
                ]
            );

            Log::info('Audit log updated after correction', [
                'form_code' => $formCode,
                'batch_id' => $batch->id,
                'new_score' => $auditResult['score'],
                'new_status' => $auditResult['status'],
            ]);

            // Recalculate batch average score
            $batchAverageScore = ComplianceAuditLog::where('batch_id', $batch->id)
                ->avg('audit_score');

            $batch->update(['audit_score' => round($batchAverageScore)]);

            $confidenceLabel = $batchAverageScore >= 90 ? 'Inspection Ready' : 
                ($batchAverageScore >= 70 ? 'Moderate Risk – Review Recommended' : 
                'High Risk – Immediate Correction Required');

            return [
                'status' => 'success',
                'form_code' => $formCode,
                'form_score' => $auditResult['score'],
                'batch_average_score' => round($batchAverageScore),
                'audit_status' => $auditResult['status'],
                'violations' => $auditResult['violations'],
                'confidence_label' => $confidenceLabel,
                'file_path' => $filePath
            ];

        } catch (\Exception $e) {
            Log::error('Violation correction failed', [
                'form_code' => $formCode,
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
