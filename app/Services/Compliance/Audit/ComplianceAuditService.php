<?php

namespace App\Services\Compliance\Audit;

class ComplianceAuditService
{
    public function audit(string $formCode, array $preparedData): array
    {
        $validator = app(\App\Services\Compliance\Validation\ComplianceFormValidator::class);
        $validationResult = $validator->validate($formCode, $preparedData);

        if (!$validationResult['valid']) {
            return [
                'status' => 'failed',
                'score' => 0,
                'violations' => $validationResult['violations']
            ];
        }

        $violations = [];
        $score = 100;

        // Validate header fields
        $headerViolations = $this->validateHeader($formCode, $preparedData);
        $violations = array_merge($violations, $headerViolations);

        // Validate row fields
        $rowViolations = $this->validateRows($formCode, $preparedData);
        $violations = array_merge($violations, $rowViolations);

        // Apply statutory rules if configured
        $ruleViolations = $this->applyStatutoryRules($formCode, $preparedData);
        $violations = array_merge($violations, $ruleViolations);

        // Calculate score
        $violationCount = count($violations);
        $score = max(0, 100 - ($violationCount * 5));

        return [
            'status' => $score >= 70 ? 'passed' : 'failed',
            'score' => $score,
            'violations' => $violations,
        ];
    }

    public function reAuditForm(
        string $formCode,
        int $tenantId,
        int $branchId,
        int $month,
        int $year,
        int $batchId
    ): array {
        try {
            $factory = app(\App\Services\Compliance\FormGenerator\FormGeneratorFactory::class);
            $generator = $factory::make($formCode);

            if (!$generator) {
                return ['status' => 'error', 'message' => 'Generator not found'];
            }

            $aggregator = app(\App\Services\Compliance\FormGenerator\FormDataAggregator::class);
            $rawData = $aggregator->aggregate($formCode, $tenantId, $branchId, $month, $year);

            $reflection = new \ReflectionClass($generator);
            $prepareMethod = $reflection->getMethod('prepareData');
            $prepareMethod->setAccessible(true);
            $preparedData = $prepareMethod->invoke($generator, $rawData);

            $auditResult = $this->audit($formCode, $preparedData);

            // CRITICAL: Create or update audit log
            \App\Models\ComplianceAuditLog::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'batch_id' => $batchId,
                    'form_code' => $formCode,
                ],
                [
                    'audit_score' => $auditResult['score'],
                    'status' => $auditResult['status'],
                    'violations' => $auditResult['violations'],
                    'updated_at' => now(),
                ]
            );

            \Log::info('Re-audit completed', [
                'form_code' => $formCode,
                'batch_id' => $batchId,
                'score' => $auditResult['score'],
                'status' => $auditResult['status'],
            ]);

            return [
                'status' => 'success',
                'new_score' => $auditResult['score'],
                'violations' => $auditResult['violations'],
                'audit_status' => $auditResult['status'],
            ];
        } catch (\Exception $e) {
            \Log::error('Re-audit failed', [
                'form_code' => $formCode,
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function auditBatch(int $batchId): array
    {
        $batch = \App\Models\ComplianceExecutionBatch::find($batchId);
        if (!$batch) return ['status' => 'error', 'message' => 'Batch not found'];

        $forms = \App\Models\ComplianceBatchForm::where('batch_id', $batchId)
            ->where('status', 'generated')
            ->get();

        if ($forms->isEmpty()) {
            return ['status' => 'no_forms', 'message' => 'No forms to audit'];
        }

        $branchId = $batch->branch_id ?? \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe($batch->tenant_id, $batch->branch_id);
        $auditResults = [];

        foreach ($forms as $form) {
            $result = $this->reAuditForm(
                $form->form_code,
                $batch->tenant_id,
                $branchId,
                $batch->period_month,
                $batch->period_year,
                $batchId
            );
            $auditResults[$form->form_code] = $result;
        }

        // Calculate batch average score
        $auditLogs = \App\Models\ComplianceAuditLog::where('batch_id', $batchId)->get();
        $avgScore = $auditLogs->isNotEmpty() ? round($auditLogs->avg('audit_score')) : 0;
        $passedCount = $auditLogs->where('status', 'passed')->count();
        $totalCount = $auditLogs->count();

        $batchStatus = $passedCount === $totalCount ? 'passed' : ($passedCount === 0 ? 'failed' : 'partial');

        \Log::info('Batch audit completed', [
            'batch_id' => $batchId,
            'avg_score' => $avgScore,
            'batch_status' => $batchStatus,
            'passed_forms' => $passedCount,
            'total_forms' => $totalCount,
        ]);

        return [
            'status' => 'success',
            'batch_score' => $avgScore,
            'batch_status' => $batchStatus,
            'passed_forms' => $passedCount,
            'total_forms' => $totalCount,
            'form_results' => $auditResults
        ];
    }

    private function validateHeader(string $formCode, array $data): array
    {
        $violations = [];

        if (empty($data['header'])) {
            $violations[] = [
                'field' => 'header',
                'type' => 'header',
                'message' => "Missing required header structure.",
            ];
            return $violations;
        }

        $header = $data['header'];
        $requiredHeaders = [
            'tenant.name' => $header['tenant']['name'] ?? null,
            'owner_name' => $header['owner_name'] ?? null,
            'wage_period' => $header['wage_period'] ?? null,
            'period' => $header['period'] ?? null,
        ];

        foreach ($requiredHeaders as $field => $value) {
            if (empty($value)) {
                $violations[] = [
                    'field' => "header.{$field}",
                    'type' => 'header',
                    'message' => "Missing required header field: {$field}",
                ];
            }
        }

        return $violations;
    }

    private function validateRows(string $formCode, array $data): array
    {
        $violations = [];

        // If the dataset is legally NIL, we don't need to validate rows.
        if (isset($data['is_nil']) && $data['is_nil'] === true) {
            return $violations;
        }

        // Blade expects rows or entries
        if (!isset($data['rows']) && !isset($data['entries'])) {
            $violations[] = [
                'field' => "rows",
                'type' => 'structural',
                'message' => "Missing rows or entries structure for template.",
            ];
            return $violations;
        }

        return $violations;
    }

    private function applyStatutoryRules(string $formCode, array $data): array
    {
        $violations = [];

        $rules = config("tn_statutory_rules.{$formCode}");

        if (!$rules || !is_array($rules)) {
            return $violations;
        }

        // Apply minimum wage rule
        if (isset($rules['min_wage']) && !empty($data['rows'])) {
            foreach ($data['rows'] as $index => $row) {
                $wages = $row['wages'] ?? 0;
                if ($wages > 0 && $wages < $rules['min_wage']) {
                    $violations[] = [
                        'field' => "rows[{$index}].wages",
                        'type' => 'statutory',
                        'message' => "Row {$index}: Wages below minimum wage (₹{$rules['min_wage']})",
                    ];
                }
            }
        }

        // Apply max working hours rule
        if (isset($rules['max_hours']) && !empty($data['rows'])) {
            foreach ($data['rows'] as $index => $row) {
                $hours = $row['hours_worked'] ?? 0;
                if ($hours > $rules['max_hours']) {
                    $violations[] = [
                        'field' => "rows[{$index}].hours_worked",
                        'type' => 'statutory',
                        'message' => "Row {$index}: Hours exceed statutory limit ({$rules['max_hours']})",
                    ];
                }
            }
        }

        return $violations;
    }
}
