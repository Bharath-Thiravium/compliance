<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // All active employees for this branch
        $employees = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->where('e.status', 'active')
            ->whereNull('e.deleted_at')
            ->select([
                'e.id',
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
            ])
            ->orderBy('e.employee_code')
            ->get();

        // Payroll cycle for the period
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->value('id');

        // Deductions keyed by employee_id
        $deductionMap = [];
        if ($cycleId && $employees->isNotEmpty()) {
            DB::table('workforce_payroll_entry')
                ->where('payroll_cycle_id', $cycleId)
                ->whereIn('employee_id', $employees->pluck('id'))
                ->select(['employee_id', 'other_deductions', 'fines', 'payment_date'])
                ->get()
                ->each(function ($entry) use (&$deductionMap) {
                    $deductionMap[$entry->employee_id] = (array) $entry;
                });
        }

        $records = [];
        foreach ($employees as $emp) {
            $emp     = (array) $emp;
            $payroll = $deductionMap[$emp['id']] ?? [];

            $records[] = [
                'employee_code'      => $emp['employee_code'],
                'employee_name'      => $emp['employee_name'],
                'father_name'        => $emp['father_name']    ?? '',
                'designation'        => $emp['designation']    ?? '',
                'damage_particulars' => '',
                'damage_date'        => '',
                'showed_cause'       => '',
                'witness_name'       => '',
                'deduction_amount'   => (float) ($payroll['other_deductions'] ?? $payroll['fines'] ?? 0),
                'instalments'        => '',
                'first_month'        => '',
                'last_month'         => '',
                'remarks'            => '',
            ];
        }

        return [
            'records' => $records,
            'meta'    => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month'     => $month,
                'year'      => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
