<?php

namespace App\Services\Compliance;

use Carbon\Carbon;

class ManualDataAdapter
{
    public function __construct(
        private ManualStatutoryDataRepository $repository
    ) {}

    public function adaptForFormGenerator(string $formCode, int $tenantId, int $branchId, int $month, int $year): array
    {
        $manualData = $this->repository->get($tenantId, $month, $year);
        
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period_month' => $month,
            'period_year' => $year,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'records' => $this->convertToRecords($manualData, $formCode),
            'config' => config("compliance_forms.{$formCode}", []),
        ];
    }

    private function convertToRecords(array $manualData, string $formCode): \Illuminate\Support\Collection
    {
        $records = [];

        // Convert manual data to database-like records
        if (!empty($manualData['employees'])) {
            foreach ($manualData['employees'] as $employee) {
                $records[] = (object) array_merge(
                    $employee,
                    $manualData['wages'] ?? [],
                    $manualData['attendance'] ?? []
                );
            }
        }

        return collect($records);
    }
}
