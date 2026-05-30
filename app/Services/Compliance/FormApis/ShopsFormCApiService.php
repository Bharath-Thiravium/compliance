<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsFormCApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_employee as e', 'e.id', '=', 'pe.employee_id')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('pe.tenant_id', $tenantId)
            ->where('pe.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->whereMonth('pc.period_from', $month)
            ->select([
                'e.id as employee_id',
                'e.employee_code',
                'e.name as employee_name',
                'e.father_name',
                'e.date_of_birth',
                'e.designation',
                'pe.total_days_worked as days_worked',
                'pe.gross_salary as total_wages',
                DB::raw('COALESCE(pe.professional_tax, 0) as tax_deducted'),
                DB::raw('COALESCE(pe.other_deductions, 0) as loss_deduction'),
            ])
            ->orderBy('e.employee_code')
            ->get();

        // Pull bonus records for these employees — financial_year covers the period
        $employeeIds = $rows->pluck('employee_id');
        $bonusMap = DB::table('bonus_records')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('employee_id', $employeeIds)
            ->whereNull('deleted_at')
            ->select('employee_id', 'bonus_amount', 'payment_date', 'financial_year')
            ->get()
            ->keyBy('employee_id');

        $records = $rows->map(function ($row) use ($bonusMap) {
            $row    = (array) $row;
            $bonus  = isset($bonusMap[$row['employee_id']]) ? (array) $bonusMap[$row['employee_id']] : [];
            return array_merge($row, [
                'bonus_amount'        => (float) ($bonus['bonus_amount']  ?? 0),
                'puja_bonus'          => 0,
                'interim_bonus'       => 0,
                'bonus_paid'          => (float) ($bonus['bonus_amount']  ?? 0),
                'bonus_payment_date'  => $bonus['payment_date']           ?? null,
            ]);
        })->toArray();

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
