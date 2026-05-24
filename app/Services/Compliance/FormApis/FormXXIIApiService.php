<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXXIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Step 1: all employees for this branch — no joins that can drop rows
        $employees = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->whereNull('e.deleted_at')
            ->select([
                'e.id',
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.designation',
                'e.basic_salary',
            ])
            ->orderBy('e.name')
            ->get();

        // Step 2: payroll cycle for the period (may not exist)
        $cycleId = DB::table('workforce_payroll_cycle')
            ->where('tenant_id', $tenantId)
            ->whereYear('period_from', $year)
            ->whereMonth('period_from', $month)
            ->value('id');

        $employeeIds = $employees->pluck('id');

        // Step 3: payroll entries keyed by employee_id (only if cycle exists)
        $payrollMap = [];
        if ($cycleId && $employees->isNotEmpty()) {
            DB::table('workforce_payroll_entry')
                ->where('payroll_cycle_id', $cycleId)
                ->whereIn('employee_id', $employeeIds)
                ->select(['employee_id', 'advances', 'payment_date'])
                ->get()
                ->each(function ($entry) use (&$payrollMap) {
                    $payrollMap[$entry->employee_id] = (array) $entry;
                });
        }

        // Step 4: dedicated workforce_advances table — more granular than payroll lump
        $tableAdvances = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('workforce_advances')) {
            $tableAdvances = DB::table('workforce_advances')
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->whereBetween('advance_date', [$this->periodStart, $this->periodEnd])
                ->whereNull('deleted_at')
                ->whereIn('employee_id', $employeeIds)
                ->select([
                    'employee_id',
                    'amount as advance_amount',
                    'advance_date',
                    'num_instalments as installments',
                    'first_month',
                    'last_month as installment_repaid',
                    'remarks',
                ])
                ->get()
                ->groupBy('employee_id');
        }

        // Step 5: merge into records array — workforce_advances takes priority
        $records = [];
        foreach ($employees as $emp) {
            $emp   = (array) $emp;
            $empId = $emp['id'];

            if ($tableAdvances->has($empId)) {
                // One record per advance entry
                foreach ($tableAdvances->get($empId) as $adv) {
                    $adv = (array) $adv;
                    $records[] = array_merge($emp, [
                        'advance_amount'        => (float) ($adv['advance_amount']    ?? 0),
                        'advance_date'          => $adv['advance_date']               ?? null,
                        'purpose'               => 'Salary Advance',
                        'installments'          => $adv['installments']               ?? 1,
                        'installment_repaid'    => $adv['installment_repaid']         ?? null,
                        'last_installment_date' => $adv['last_month']                 ?? null,
                        'remarks'               => $adv['remarks']                   ?? null,
                    ]);
                }
            } else {
                // Fall back to payroll_entry.advances lump
                $payroll = $payrollMap[$empId] ?? [];
                $records[] = array_merge($emp, [
                    'advance_amount'        => (float) ($payroll['advances']    ?? 0),
                    'advance_date'          => $payroll['payment_date']         ?? null,
                    'purpose'               => 'Salary Advance',
                    'installments'          => 1,
                    'installment_repaid'    => null,
                    'last_installment_date' => null,
                    'remarks'               => null,
                ]);
            }
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
