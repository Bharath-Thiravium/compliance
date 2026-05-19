<?php

namespace App\Services\Compliance;

use App\Models\StatutoryManualData;

class ManualStatutoryDataRepository
{
    public function get(int $tenantId, int $month, int $year): array
    {
        $data = StatutoryManualData::where('tenant_id', $tenantId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$data) {
            return $this->getEmptyStructure();
        }

        return [
            'establishment' => $data->establishment_details ?? [],
            'employer' => $data->employer_details ?? [],
            'employees' => $data->employee_summary ?? [],
            'wages' => $data->wage_summary ?? [],
            'attendance' => $data->attendance_summary ?? [],
            'accidents' => $data->accident_details ?? [],
            'contractors' => $data->contractor_summary ?? [],
        ];
    }

    public function save(int $tenantId, int $month, int $year, array $data): void
    {
        StatutoryManualData::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'month' => $month,
                'year' => $year,
            ],
            [
                'establishment_details' => $data['establishment'] ?? null,
                'employer_details' => $data['employer'] ?? null,
                'employee_summary' => $data['employees'] ?? null,
                'wage_summary' => $data['wages'] ?? null,
                'attendance_summary' => $data['attendance'] ?? null,
                'accident_details' => $data['accidents'] ?? null,
                'contractor_summary' => $data['contractors'] ?? null,
            ]
        );
    }

    private function getEmptyStructure(): array
    {
        return [
            'establishment' => [],
            'employer' => [],
            'employees' => [],
            'wages' => [],
            'attendance' => [],
            'accidents' => [],
            'contractors' => [],
        ];
    }
}
