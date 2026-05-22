<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;

class StrictDataValidator
{
    public function validateFormData(string $formCode, array $data): void
    {
        // ✅ If NIL form, skip strict validation
        if (!empty($data['is_nil']) && $data['is_nil'] === true) {
            logger()->info("{$formCode} generated as NIL — skipping strict validation.");
            return;
        }

        if (empty($data['rows'])) {
            return;
        }

        foreach ($data['rows'] as $index => $row) {
            $this->validateRow($formCode, $row, $index);
        }

        $this->validateHeader($formCode, $data['header']);
    }

    private function validateRow(string $formCode, array $row, int $index): void
    {
        $requiredFields = $this->getRequiredFieldsForForm($formCode);

        foreach ($requiredFields as $field) {
            // Accept either 'name' or 'employee_name' as the employee name field
            $aliases = ['name' => ['name', 'employee_name'], 'employee_name' => ['employee_name', 'name']];
            $candidates = $aliases[$field] ?? [$field];
            $found = false;
            foreach ($candidates as $candidate) {
                if (isset($row[$candidate]) && $row[$candidate] !== 'N/A') {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new \RuntimeException(
                    "Missing required field '{$field}' in {$formCode} row " . ($index + 1)
                );
            }
        }
    }

    private function validateHeader(string $formCode, array $header): void
    {
        // Support both flat header (establishment_name) and nested (tenant.name)
        $tenantName = $header['establishment_name']
            ?? $header['owner_name']
            ?? (is_array($header['tenant'] ?? null) ? ($header['tenant']['name'] ?? null) : ($header['tenant'] ?? null));

        if (empty($tenantName)) {
            throw new \RuntimeException("{$formCode}: Missing tenant establishment name");
        }

        if (empty($header['branch']['name'] ?? null)) {
            logger()->warning("{$formCode}: Missing branch unit name");
        }

        if (empty($header['branch']['address'] ?? null)) {
            logger()->warning("{$formCode}: Missing branch address");
        }

        $ruleConfig = config("tn_statutory_rules.{$formCode}");
        if (!$ruleConfig) {
            logger()->warning("{$formCode}: Rule config missing — generating NIL form.");
        }
    }

    private function getRequiredFieldsForForm(string $formCode): array
    {
        $employeeBasedForms = [
            'FORM_10', 'Form10',
            'FORM_B',  'FormB',
            'FORM_25', 'Form25',
            'FORM_XXIII', 'FormXXIII',
            'SHOPS_FORM_12', 'ShopsForm12',
        ];

        if (in_array($formCode, $employeeBasedForms)) {
            return ['employee_code', 'name'];
        }

        return [];
    }

    public function validateTenantSetup(int $tenantId): array
    {
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        if (!$tenant) {
            return ['valid' => false, 'errors' => ['Tenant not found']];
        }

        $errors = [];

        $name = $tenant->establishment_name ?? $tenant->name;
        if (empty($name)) {
            $errors[] = 'Missing establishment name';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }

    public function validateBranchSetup(int $branchId): array
    {
        $branch = DB::table('branches')->where('id', $branchId)->first();

        if (!$branch) {
            return ['valid' => false, 'errors' => ['Branch not found']];
        }

        $errors = [];
        $name = $branch->unit_name ?? $branch->branch_name;
        if (empty($name)) {
            $errors[] = 'Missing unit name';
        }

        // address is optional — do not block generation
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
}
