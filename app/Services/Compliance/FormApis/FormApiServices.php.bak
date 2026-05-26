<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class Form10ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->where('pe.overtime_hours', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'pe.overtime_hours',
                'pe.overtime_wages',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class Form25ApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'pe.total_days_worked',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormAApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'e.date_of_joining',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormCApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'pe.advances',
                'pe.fines',
                'pe.total_deductions',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormDApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_attendance as wa')
            ->join('workforce_employee as e', 'e.id', '=', 'wa.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('wa.attendance_date', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'wa.attendance_date',
                'wa.status',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contractor_master as cm')
            ->where('cm.tenant_id', $tenantId)
            ->select([
                'cm.company_name',
                'cm.license_number',
                'cm.valid_from',
                'cm.valid_to',
            ])
            ->orderBy('cm.company_name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.name as worker_name',
                'cm.company_name as contractor_name',
                'cld.deployment_start',
                'cld.deployment_end',
                'cld.wage_rate',
            ])
            ->orderBy('e.name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXVIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'cm.company_name as contractor_name',
                'cld.wage_rate',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXVIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'cm.company_name as contractor_name',
                'cld.wage_rate',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXIXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'cm.company_name as contractor_name',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_deductions as d')
            ->join('workforce_employee as e', 'e.id', '=', 'd.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('d.deduction_date', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.name as employee_name',
                'e.designation',
                'd.particulars as damage_particulars',
                'd.deduction_date as damage_date',
                'd.amount as deduction_amount',
            ])
            ->orderBy('d.deduction_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXXIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_fines as f')
            ->join('workforce_employee as e', 'e.id', '=', 'f.employee_id')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereBetween('f.fine_date', [$this->periodStart, $this->periodEnd])
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'f.fine_date',
                'f.reason',
                'f.amount as fine_amount',
            ])
            ->orderBy('f.fine_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}

class FormXXIIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contract_labour_deployment as cld')
            ->join('contractor_master as cm', 'cm.id', '=', 'cld.contractor_id')
            ->join('workforce_employee as e', 'e.id', '=', 'cld.employee_id')
            ->where('cld.tenant_id', $tenantId)
            ->where('cld.branch_id', $branchId)
            ->whereBetween('cld.deployment_start', [$this->periodStart, $this->periodEnd])
            ->where('cld.overtime_hours', '>', 0)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                'e.designation',
                'cm.company_name as contractor_name',
                'cld.overtime_hours',
                'cld.overtime_wages',
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'month' => $month,
            'year' => $year,
            'period' => $this->formatPeriod(),
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'rows' => $rows,
            'record_count' => count($rows),
        ];
    }
}
