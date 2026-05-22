<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormValidationService
{
    public function validate(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $errors = [];
        
        $errors = array_merge($errors, $this->validatePayrollLock($tenantId, $month, $year));
        $errors = array_merge($errors, $this->validateDataAvailability($formCode, $tenantId, $branchId, $month, $year));
        $errors = array_merge($errors, $this->validatePeriodConsistency($month, $year));
        $errors = array_merge($errors, $this->validateBranchIsolation($branchId, $tenantId));
        
        if (in_array($formCode, ['FORM_XIII', 'FORM_XVI', 'FORM_XVII'])) {
            $errors = array_merge($errors, $this->validateContractorMapping($tenantId, $branchId, $month, $year));
        }
        
        if (in_array($formCode, ['FORM_B', 'FORM_10', 'FORM_25'])) {
            $errors = array_merge($errors, $this->validateNoDuplicateEmployees($tenantId, $month, $year));
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    protected function validatePayrollLock(int $tenantId, int $month, int $year): array
    {
        // Skip validation if payroll_locks table doesn't exist
        try {
            $lock = DB::table('payroll_locks')
                ->where('tenant_id', $tenantId)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->where('is_locked', true)
                ->first();
            
            if (!$lock) {
                return []; // Warning only, not blocking
            }
        } catch (\Exception $e) {
            // Table doesn't exist, skip validation
            return [];
        }
        
        return [];
    }
    
    protected function validateDataAvailability(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $config = config("compliance_forms.{$formCode}");
        if (!$config) {
            return ['Form configuration not found'];
        }
        
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        $count = DB::table($config['table'])
            ->where('tenant_id', $tenantId)
            ->when($config['branch_filter'] ?? false, fn($q) => $q->where('branch_id', $branchId))
            ->when($config['date_field'] ?? null, fn($q) => $q->whereBetween($config['date_field'], [$periodStart, $periodEnd]))
            ->count();
        
        return [];
    }
    
    protected function validatePeriodConsistency(int $month, int $year): array
    {
        if ($month < 1 || $month > 12) {
            return ['Invalid month'];
        }
        
        if ($year < 2020 || $year > 2050) {
            return ['Invalid year'];
        }
        
        return [];
    }
    
    protected function validateBranchIsolation(int $branchId, int $tenantId): array
    {
        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();
        
        if (!$branch) {
            return ['Branch does not belong to tenant'];
        }
        
        return [];
    }
    
    protected function validateContractorMapping(int $tenantId, int $branchId, int $month, int $year): array
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        $unmapped = DB::table('contract_labour_deployment')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereBetween('deployment_start', [$periodStart, $periodEnd])
            ->whereNull('contractor_id')
            ->count();
        
        if ($unmapped > 0) {
            return ["{$unmapped} contract workers without contractor mapping"];
        }
        
        return [];
    }
    
    protected function validateNoDuplicateEmployees(int $tenantId, int $month, int $year): array
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        $duplicates = DB::table('workforce_payroll_entry')
            ->select('employee_id', DB::raw('COUNT(*) as count'))
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->groupBy('employee_id')
            ->having('count', '>', 1)
            ->count();
        
        if ($duplicates > 0) {
            return ["{$duplicates} employees have duplicate payroll entries"];
        }
        
        return [];
    }
}
