<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormDataAggregator
{
    public function aggregate(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $config = config("compliance_forms.{$formCode}");
        
        if (!$config) {
            throw new \Exception("Form configuration not found for {$formCode}");
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

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
        
        $query->select($selectFields)->distinct();

        // Chunk large datasets to reduce memory
        $data = collect();
        $query->orderBy($table . '.id')->chunk(500, function($records) use (&$data) {
            $data = $data->merge($records);
        });

        // Demo data fallback for empty results
        if ($data->isEmpty() && config('app.demo_mode', false)) {
            return \App\Services\Compliance\DemoDataProvider::for($formCode, $tenantId, $branchId, $month, $year);
        }

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period_month' => $month,
            'period_year' => $year,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'records' => $data,
            'config' => $config,
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
}
