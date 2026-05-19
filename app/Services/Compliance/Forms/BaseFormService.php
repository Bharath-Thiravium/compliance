<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;

abstract class BaseFormService
{
    protected int $tenantId;
    protected int $branchId;
    protected int $month;
    protected int $year;

    public function __construct()
    {
    }

    abstract public function generate(int $tenantId, int $branchId, int $month, int $year): array;

    protected function getDateRange(): array
    {
        $startDate = now()->setYear($this->year)->setMonth($this->month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        return [$startDate, $endDate];
    }

    protected function buildResponse(array $rows, array $totals = []): array
    {
        return [
            'header' => $this->getHeader(),
            'rows' => $rows,
            'totals' => $totals,
            'period_month' => $this->month,
            'period_year' => $this->year,
            'period' => "{$this->month}/{$this->year}",
            'status' => empty($rows) ? 'NIL' : 'SUCCESS',
        ];
    }

    protected function getHeader(): array
    {
        $tenant = DB::table('tenants')->where('id', $this->tenantId)->first();
        $branch = DB::table('branches')->where('id', $this->branchId)->first();

        return [
            'tenant_name' => $tenant?->name ?? 'N/A',
            'tenant_address' => $tenant?->address ?? 'N/A',
            'branch_name' => $branch?->branch_name ?? 'N/A',
            'branch_address' => $branch?->address ?? 'N/A',
            'period_month' => $this->month,
            'period_year' => $this->year,
        ];
    }

    protected function nilResponse(): array
    {
        return $this->buildResponse([]);
    }
}
