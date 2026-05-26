<?php

namespace App\Services\Compliance;

use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceFormSource;
use App\Models\WorkforcePayrollEntry;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FormDataAggregator
{
    /**
     * Aggregate data for a specific form
     */
    public function aggregate(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $config = config("compliance_forms.{$formCode}");
        
        if (!$config) {
            throw new \Exception("Form configuration not found for {$formCode}");
        }

        $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $table = $config['table'];
        $fields = $config['fields'];

        $query = DB::table($table);
        
        // Apply tenant filter if table has tenant_id column
        if (DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
            $query->where($table . '.tenant_id', $tenantId);
        }

        if (isset($config['branch_filter']) && $config['branch_filter']) {
            $query->where($table . '.branch_id', $branchId);
        }

        // Special handling for workforce_payroll_entry: filter by payroll cycle period
        if ($table === 'workforce_payroll_entry') {
            $query->join('workforce_payroll_cycle', 'workforce_payroll_entry.payroll_cycle_id', '=', 'workforce_payroll_cycle.id');
            $query->whereYear('workforce_payroll_cycle.period_from', $year)
                  ->whereMonth('workforce_payroll_cycle.period_from', $month);
        } elseif (isset($config['date_field'])) {
            $query->whereBetween($table . '.' . $config['date_field'], [$periodStart, $periodEnd]);
        }

        if (isset($config['joins'])) {
            foreach ($config['joins'] as $join) {
                $query->join($join['table'], $join['first'], $join['operator'], $join['second']);
                
                // Apply tenant filter on joined table if it has tenant_id
                if (DB::getSchemaBuilder()->hasColumn($join['table'], 'tenant_id')) {
                    $query->where($join['table'] . '.tenant_id', $tenantId);
                }
            }
        }

        $selectFields = [$table . '.*'];
        if (!empty($fields)) {
            $selectFields = [];
            foreach ($fields as $alias => $column) {
                $selectFields[] = $column . ' as ' . $alias;
            }
        }
        
        $query->select($selectFields);

        // Chunk large datasets to reduce memory
        $data = collect();
        $query->orderBy($table . '.id')->chunk(500, function($records) use (&$data) {
            $data = $data->merge($records);
        });

        // Convert all stdClass objects to plain arrays — critical for live server parity
        $dataArray = $data->map(fn($row) => (array) $row)->values()->toArray();

        // Demo data fallback for empty results
        if (empty($dataArray) && config('app.demo_mode', false)) {
            return \App\Services\Compliance\DemoDataProvider::for($formCode, $tenantId, $branchId, $month, $year);
        }

        return [
            'tenant_id'    => $tenantId,
            'branch_id'    => $branchId,
            'period_month' => $month,
            'period_year'  => $year,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end'   => $periodEnd->format('Y-m-d'),
            'records'      => $dataArray,
            'config'       => $config,
            'meta'         => ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
            'tenant'       => $this->getTenantDetails($tenantId),
            'branch'       => $this->getBranchDetails($branchId, $tenantId),
            'period'       => \Carbon\Carbon::create($year, $month, 1)->format('F Y'),
        ];
    }

    public function getBranchDetails(int $branchId, ?int $tenantId = null): array
    {
        $query = DB::table('branches')
            ->select('branch_name', 'unit_name', 'address', 'factory_license_number', 'pf_code', 'esi_code', 'tenant_id')
            ->where('id', $branchId);
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        $branch = $query->first();
        
        if (!$branch) {
            \Log::warning("Branch {$branchId} not found, using fallback values");
            return [
                'name' => 'Unit Name Not Configured',
                'address' => 'Address Not Configured',
                'license' => '',
                'pf_code' => '',
                'esi_code' => '',
            ];
        }

        return [
            'name' => $branch->unit_name ?? $branch->branch_name ?? 'Unit Name Not Configured',
            'address' => $branch->address ?? 'Address Not Configured',
            'license' => $branch->factory_license_number ?? '',
            'pf_code' => $branch->pf_code ?? '',
            'esi_code' => $branch->esi_code ?? '',
        ];
    }

    public function getTenantDetails(int $tenantId): array
    {
        $tenant = DB::table('tenants')
            ->select('name', 'establishment_name', 'factory_license_no', 'pf_code', 'esi_code', 'subscription_type')
            ->where('id', $tenantId)
            ->first();
        
        if (!$tenant) {
            \Log::warning("Tenant {$tenantId} not found, using fallback values");
            return [
                'name' => 'Establishment Name Not Configured',
                'factory_license_no' => '',
                'pf_code' => '',
                'esi_code' => '',
                'subscription' => 'FULL',
            ];
        }

        $name = $tenant->establishment_name ?? $tenant->name ?? 'Establishment Name Not Configured';

        return [
            'name' => $name,
            'factory_license_no' => $tenant->factory_license_no ?? '',
            'pf_code' => $tenant->pf_code ?? '',
            'esi_code' => $tenant->esi_code ?? '',
            'subscription' => $tenant->subscription_type ?? 'FULL',
        ];
    }

    public function aggregateData(ComplianceFormsMaster $form, string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $sources = $form->sources;

        if ($sources->isEmpty()) {
            return match ($form->form_code) {
                'FORM_A', 'FORM_B' => $this->aggregateWageRegister($periodFrom, $periodTo, $branchId),
                'FORM_C' => $this->aggregateOTRegister($periodFrom, $periodTo, $branchId),
                'FORM_XX' => $this->aggregateDeductionRegister($periodFrom, $periodTo, $branchId),
                'CLRA_WAGE' => $this->aggregateCLRAWage($periodFrom, $periodTo, $branchId),
                'BONUS_REGISTER' => $this->aggregateBonus($periodFrom, $periodTo, $branchId),
                'ATTENDANCE_REGISTER' => $this->aggregateAttendance($periodFrom, $periodTo, $branchId),
                default => [],
            };
        }

        return $this->aggregateFromSources($sources, $periodFrom, $periodTo, $branchId);
    }

    private function aggregateFromSources($sources, string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $data = [];

        foreach ($sources as $source) {
            $methodName = 'aggregate' . str_replace('_', '', ucwords($source->source_type, '_'));

            if (method_exists($this, $methodName)) {
                $data[$source->source_type] = $this->$methodName($periodFrom, $periodTo, $branchId);
            }
        }

        return $data;
    }

    public function aggregateWageRegister(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = WorkforcePayrollEntry::with('employee')
            ->whereHas('payrollCycle', function ($q) use ($periodFrom, $periodTo) {
                $q->where('period_from', '>=', $periodFrom)
                    ->where('period_to', '<=', $periodTo);
            });

        if ($branchId) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        $entries = $query->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_employees' => $entries->count(),
            'total_wages' => $entries->sum('gross_salary'),
            'total_deductions' => $entries->sum('total_deductions'),
            'net_payable' => $entries->sum('net_salary'),
            'entries' => $entries->map(fn($e) => [
                'employee_code' => $e->employee->employee_code ?? '',
                'employee_name' => $e->employee->name ?? '',
                'basic' => $e->basic_earned,
                'da' => $e->da_earned,
                'hra' => $e->hra_earned,
                'gross' => $e->gross_salary,
                'pf' => $e->pf_employee,
                'esi' => $e->esi_employee,
                'net' => $e->net_salary,
            ])->toArray(),
        ];
    }

    public function aggregateOTRegister(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = WorkforcePayrollEntry::with('employee')
            ->whereHas('payrollCycle', function ($q) use ($periodFrom, $periodTo) {
                $q->whereBetween('period_from', [$periodFrom, $periodTo]);
            })
            ->where('overtime_hours', '>', 0);

        if ($branchId) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        $entries = $query->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_ot_hours' => $entries->sum('overtime_hours'),
            'total_ot_wages' => $entries->sum('overtime_wages'),
            'entries' => $entries->map(fn($e) => [
                'employee_code' => $e->employee->employee_code ?? '',
                'employee_name' => $e->employee->name ?? '',
                'ot_hours' => $e->overtime_hours,
                'ot_wages' => $e->overtime_wages,
            ])->toArray(),
        ];
    }
    public function aggregateDeductionRegister(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $tenantId = Auth::user()?->tenant_id ?? 1;
        $query = DB::table('workforce_payroll_entry as wpe')
            ->join('workforce_employee as we', 'wpe.employee_id', '=', 'we.id')
            ->join('workforce_payroll_cycle as wpc', 'wpe.payroll_cycle_id', '=', 'wpc.id')
            ->whereBetween('wpc.period_from', [$periodFrom, $periodTo])
            ->where('wpe.tenant_id', $tenantId)
            ->where('we.tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('fines', '>', 0)
                    ->orWhere('other_deductions', '>', 0);
            });

        if ($branchId) {
            $query->where('we.branch_id', $branchId);
        }

        $entries = $query->select([
            'we.employee_code',
            'we.name',
            'we.designation',
            'wpe.fines',
            'wpe.other_deductions',
            'wpc.period_from'
        ])->get();

        return [
            'period_from'       => $periodFrom,
            'period_to'         => $periodTo,
            'total_deductions'  => $entries->sum('fines') + $entries->sum('other_deductions'),
            'entries'           => $entries->map(fn($r) => (array) $r)->values()->toArray(),
        ];
    }

    public function aggregateCLRAWage(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $tenantId = Auth::user()?->tenant_id ?? 1;
        $query = DB::table('contract_labour_deployment as cld')
            ->join('workforce_payroll_entry as wpe', 'cld.employee_id', '=', 'wpe.employee_id')
            ->join('workforce_payroll_cycle as wpc', 'wpe.payroll_cycle_id', '=', 'wpc.id')
            ->join('contractor_master as cm', 'cld.contractor_id', '=', 'cm.id')
            ->whereBetween('wpc.period_from', [$periodFrom, $periodTo])
            ->where('cld.tenant_id', $tenantId)
            ->where('wpe.tenant_id', $tenantId);

        if ($branchId) {
            $query->where('cld.branch_id', $branchId);
        }

        $entries = $query->select([
            'cm.company_name as contractor_name',
            'wpe.employee_id',
            'wpe.gross_salary',
            'wpe.net_salary',
            'cld.wage_rate',
        ])->get();

        return [
            'period_from'            => $periodFrom,
            'period_to'              => $periodTo,
            'total_contract_workers' => $entries->count(),
            'total_wages'            => $entries->sum('net_salary'),
            'entries'                => $entries->map(fn($r) => (array) $r)->values()->toArray(),
        ];
    }

    public function aggregateBonus(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $tenantId = Auth::user()?->tenant_id ?? 1;
        $query = DB::table('workforce_bonus_record as wbr')
            ->join('workforce_employee as we', 'wbr.employee_id', '=', 'we.id')
            ->whereBetween('wbr.payment_date', [$periodFrom, $periodTo])
            ->where('wbr.tenant_id', $tenantId)
            ->where('we.tenant_id', $tenantId);

        if ($branchId) {
            $query->where('we.branch_id', $branchId);
        }

        $entries = $query->select([
            'we.employee_code',
            'we.name',
            'wbr.bonus_percentage',
            'wbr.bonus_amount',
            'wbr.payment_date',
        ])->get();

        return [
            'period_from'      => $periodFrom,
            'period_to'        => $periodTo,
            'total_bonus_paid' => $entries->sum('bonus_amount'),
            'entries'          => $entries->map(fn($r) => (array) $r)->values()->toArray(),
        ];
    }

    public function aggregateAttendance(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $tenantId = Auth::user()?->tenant_id ?? 1;
        $query = DB::table('workforce_attendance as wa')
            ->join('workforce_employee as we', 'wa.employee_id', '=', 'we.id')
            ->whereBetween('wa.date', [$periodFrom, $periodTo])
            ->where('wa.tenant_id', $tenantId)
            ->where('we.tenant_id', $tenantId);

        if ($branchId) {
            $query->where('we.branch_id', $branchId);
        }

        $entries = $query->select([
            'we.employee_code',
            'we.name',
            'wa.date',
            'wa.status',
            'wa.hours_worked',
        ])->get();

        return [
            'period_from'   => $periodFrom,
            'period_to'     => $periodTo,
            'total_records' => $entries->count(),
            'entries'       => $entries->map(fn($r) => (array) $r)->values()->toArray(),
        ];
    }
}
