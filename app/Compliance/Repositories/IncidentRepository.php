<?php

namespace App\Compliance\Repositories;

use App\Models\IncidentDocument;
use Illuminate\Support\Collection;

class IncidentRepository
{
    public function getByPeriod(int $tenantId, int $month, int $year): Collection
    {
        return IncidentDocument::where('tenant_id', $tenantId)
            ->whereYear('incident_date', $year)
            ->whereMonth('incident_date', $month)
            ->with('employee')
            ->get();
    }

    public function getByBranchAndPeriod(int $tenantId, int $branchId, int $month, int $year): Collection
    {
        return IncidentDocument::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereYear('incident_date', $year)
            ->whereMonth('incident_date', $month)
            ->with('employee')
            ->get();
    }

    public function getByType(int $tenantId, string $type, int $month, int $year): Collection
    {
        return IncidentDocument::where('tenant_id', $tenantId)
            ->where('incident_type', $type)
            ->whereYear('incident_date', $year)
            ->whereMonth('incident_date', $month)
            ->with('employee')
            ->get();
    }

    public function getAll(int $tenantId): Collection
    {
        return IncidentDocument::where('tenant_id', $tenantId)->get();
    }
}
