<?php

namespace App\Services\Compliance\Validation;

class CrossFormValidator
{
    public function validate(int $batchId, array $allFormsData): array
    {
        $violations = [];

        // Employee count consistency
        $violations = array_merge($violations, $this->validateEmployeeCount($allFormsData));

        // Total wages consistency
        $violations = array_merge($violations, $this->validateWagesConsistency($allFormsData));

        // Overtime hours reconciliation
        $violations = array_merge($violations, $this->validateOvertimeReconciliation($allFormsData));

        // ESI employee list consistency
        $violations = array_merge($violations, $this->validateESIConsistency($allFormsData));

        // Contractor vs principal employer matching
        $violations = array_merge($violations, $this->validateContractorMatching($allFormsData));

        return $violations;
    }

    private function validateEmployeeCount(array $allFormsData): array
    {
        $violations = [];
        $counts = [];

        // Extract employee counts from different forms
        foreach (['FORM_25', 'FORM_B', 'FORM_XIX'] as $formCode) {
            if (isset($allFormsData[$formCode]['rows'])) {
                $counts[$formCode] = count($allFormsData[$formCode]['rows']);
            }
        }

        if (count($counts) > 1 && count(array_unique($counts)) > 1) {
            $violations[] = [
                'type' => 'cross_form',
                'field' => 'employee_count',
                'message' => 'Employee count mismatch across Muster Roll, Wage Register: ' . json_encode($counts)
            ];
        }

        return $violations;
    }

    private function validateWagesConsistency(array $allFormsData): array
    {
        $violations = [];
        $wageTotals = [];

        foreach (['FORM_B', 'FORM_XVI', 'SHOPS_FORM_12'] as $formCode) {
            if (isset($allFormsData[$formCode]['totals']['total_wages'])) {
                $wageTotals[$formCode] = $allFormsData[$formCode]['totals']['total_wages'];
            }
        }

        if (count($wageTotals) > 1) {
            $uniqueTotals = array_unique($wageTotals);
            if (count($uniqueTotals) > 1) {
                $maxDiff = max($wageTotals) - min($wageTotals);
                if ($maxDiff > 10) { // Allow ₹10 rounding difference
                    $violations[] = [
                        'type' => 'cross_form',
                        'field' => 'total_wages',
                        'message' => 'Total wages mismatch across wage registers: ' . json_encode($wageTotals)
                    ];
                }
            }
        }

        return $violations;
    }

    private function validateOvertimeReconciliation(array $allFormsData): array
    {
        $violations = [];
        $overtimeHours = [];

        foreach (['FORM_10', 'FORM_XXIII'] as $formCode) {
            if (isset($allFormsData[$formCode]['totals']['total_overtime_hours'])) {
                $overtimeHours[$formCode] = $allFormsData[$formCode]['totals']['total_overtime_hours'];
            }
        }

        if (count($overtimeHours) > 1 && count(array_unique($overtimeHours)) > 1) {
            $violations[] = [
                'type' => 'cross_form',
                'field' => 'overtime_hours',
                'message' => 'Overtime hours mismatch across overtime registers: ' . json_encode($overtimeHours)
            ];
        }

        return $violations;
    }

    private function validateESIConsistency(array $allFormsData): array
    {
        $violations = [];
        $esiEmployees = [];

        if (isset($allFormsData['ESI_FORM_12']['rows'])) {
            $esiEmployees = array_column($allFormsData['ESI_FORM_12']['rows'], 'employee_id');
        }

        // Check if ESI employees exist in wage register
        if (!empty($esiEmployees) && isset($allFormsData['FORM_B']['rows'])) {
            $wageRegisterEmployees = array_column($allFormsData['FORM_B']['rows'], 'employee_id');
            $missing = array_diff($esiEmployees, $wageRegisterEmployees);
            
            if (!empty($missing)) {
                $violations[] = [
                    'type' => 'cross_form',
                    'field' => 'esi_employees',
                    'message' => 'ESI employees not found in wage register: ' . implode(', ', $missing)
                ];
            }
        }

        return $violations;
    }

    private function validateContractorMatching(array $allFormsData): array
    {
        $violations = [];

        // Check contractor forms consistency
        if (isset($allFormsData['FORM_XVI']['contractor_name']) && isset($allFormsData['FORM_XIX']['contractor_name'])) {
            if ($allFormsData['FORM_XVI']['contractor_name'] !== $allFormsData['FORM_XIX']['contractor_name']) {
                $violations[] = [
                    'type' => 'cross_form',
                    'field' => 'contractor_name',
                    'message' => 'Contractor name mismatch between FORM_XVI and FORM_XIX'
                ];
            }
        }

        return $violations;
    }
}
