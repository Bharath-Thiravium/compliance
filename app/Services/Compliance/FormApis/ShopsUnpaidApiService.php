<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class ShopsUnpaidApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fines realisation — filter directly on workforce_fines own columns
        $fines = DB::table('workforce_fines as wf')
            ->where('wf.tenant_id', $tenantId)
            ->where('wf.branch_id', $branchId)
            ->whereYear('wf.fine_date', $year)
            ->select([
                DB::raw('QUARTER(wf.fine_date) as quarter'),
                DB::raw('SUM(COALESCE(wf.amount, 0)) as fines_realisation'),
            ])
            ->groupBy(DB::raw('QUARTER(wf.fine_date)'))
            ->get()->keyBy('quarter');

        // Wage accumulations — workforce_payroll_entry joined to workforce_payroll_cycle
        // Include ALL entries for the year; payment_date NULL is unreliable as an "unpaid" signal
        $payroll = DB::table('workforce_payroll_entry as pe')
            ->join('workforce_payroll_cycle as pc', 'pc.id', '=', 'pe.payroll_cycle_id')
            ->where('pe.tenant_id', $tenantId)
            ->where('pe.branch_id', $branchId)
            ->whereYear('pc.period_from', $year)
            ->select([
                DB::raw('QUARTER(pc.period_from) as quarter'),
                DB::raw('SUM(COALESCE(pe.basic_earned, 0)) as unpaid_basic'),
                DB::raw('SUM(COALESCE(pe.overtime_wages, 0)) as unpaid_overtime'),
                DB::raw('SUM(COALESCE(pe.da_earned, 0) + COALESCE(pe.hra_earned, 0) + COALESCE(pe.other_allowances, 0)) as unpaid_allowance'),
                DB::raw('SUM(COALESCE(pe.other_deductions, 0)) as pwa_deduction'),
            ])
            ->groupBy(DB::raw('QUARTER(pc.period_from)'))
            ->get()->keyBy('quarter');

        // Standing order deductions — filter directly on workforce_deductions own columns
        $standingOrder = DB::table('workforce_deductions as wd')
            ->where('wd.tenant_id', $tenantId)
            ->where('wd.branch_id', $branchId)
            ->whereYear('wd.deduction_date', $year)
            ->select([
                DB::raw('QUARTER(wd.deduction_date) as quarter'),
                DB::raw('SUM(COALESCE(wd.amount, 0)) as standing_order_deduction'),
            ])
            ->groupBy(DB::raw('QUARTER(wd.deduction_date)'))
            ->get()->keyBy('quarter');

        $quarters = [1 => 'quarter_march', 2 => 'quarter_june', 3 => 'quarter_september', 4 => 'quarter_december'];

        $result = [];
        foreach ($quarters as $q => $key) {
            $result[$key] = [
                'fines_realisation'        => (float) ($fines[$q]->fines_realisation ?? 0),
                'unpaid_basic'             => (float) ($payroll[$q]->unpaid_basic ?? 0),
                'unpaid_overtime'          => (float) ($payroll[$q]->unpaid_overtime ?? 0),
                'unpaid_allowance'         => (float) ($payroll[$q]->unpaid_allowance ?? 0),
                'unpaid_bonus'             => 0,
                'unpaid_gratuity'          => 0,
                'unpaid_other'             => 0,
                'standing_order_deduction' => (float) ($standingOrder[$q]->standing_order_deduction ?? 0),
                'pwa_deduction'            => (float) ($payroll[$q]->pwa_deduction ?? 0),
            ];
        }

        return [
            // Use 'quarters' key — NOT 'records' — so BaseFormGenerator::generate()
            // does not run normalizeRecords() on it and destroy the associative keys.
            'quarters' => $result,
            'meta'     => ['tenant_id' => $tenantId, 'branch_id' => $branchId, 'month' => $month, 'year' => $year],
            'tenant'   => $this->getTenantDetails($tenantId),
            'branch'   => $this->getBranchDetails($branchId, $tenantId),
            'period'   => $this->formatPeriod(),
        ];
    }
}
