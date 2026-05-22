<?php

namespace App\Compliance\Repositories;

use App\Models\ContractLabourDeployment;
use App\Models\ContractorMaster;
use Illuminate\Support\Collection;

class ContractorRepository
{
    public function getDeploymentsByPeriod(int $tenantId, int $month, int $year): Collection
    {
        return ContractLabourDeployment::where('tenant_id', $tenantId)
            ->whereYear('deployment_start', $year)
            ->whereMonth('deployment_start', $month)
            ->with(['contractor', 'employee'])
            ->get();
    }

    public function getDeploymentsByBranch(int $tenantId, int $branchId, int $month, int $year): Collection
    {
        return ContractLabourDeployment::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereYear('deployment_start', $year)
            ->whereMonth('deployment_start', $month)
            ->with(['contractor', 'employee'])
            ->get();
    }

    public function getContractors(int $tenantId): Collection
    {
        return ContractorMaster::where('tenant_id', $tenantId)->get();
    }

    public function getContractorById(int $contractorId): ?ContractorMaster
    {
        return ContractorMaster::find($contractorId);
    }

    public function getActiveDeployments(int $tenantId, int $month, int $year): Collection
    {
        return ContractLabourDeployment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereYear('deployment_start', $year)
            ->whereMonth('deployment_start', $month)
            ->with(['contractor', 'employee'])
            ->get();
    }
}
