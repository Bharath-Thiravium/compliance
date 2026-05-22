<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\Session;

class ManualStatutoryDataRepository
{
    private function key(int $tenantId, int $month, int $year): string
    {
        return "manual_statutory_{$tenantId}_{$month}_{$year}";
    }

    public function get(int $tenantId, int $month, int $year): array
    {
        return Session::get($this->key($tenantId, $month, $year), $this->getEmptyStructure());
    }

    public function save(int $tenantId, int $month, int $year, array $data): void
    {
        Session::put($this->key($tenantId, $month, $year), [
            'establishment' => $data['establishment'] ?? [],
            'employer'      => $data['employer']      ?? [],
            'employees'     => $data['employees']     ?? [],
            'wages'         => $data['wages']         ?? [],
            'attendance'    => $data['attendance']    ?? [],
            'accidents'     => $data['accidents']     ?? [],
            'contractors'   => $data['contractors']   ?? [],
        ]);
    }

    private function getEmptyStructure(): array
    {
        return [
            'establishment' => [],
            'employer'      => [],
            'employees'     => [],
            'wages'         => [],
            'attendance'    => [],
            'accidents'     => [],
            'contractors'   => [],
        ];
    }
}
