<?php

namespace App\Compliance\Repositories;

use App\Models\WorkforceEmployee;
use Illuminate\Support\Collection;

class EmployeeRepository
{
    public function getByBranch(int $tenantId, int $branchId): Collection
    {
        return WorkforceEmployee::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->get();
    }

    public function getAll(int $tenantId): Collection
    {
        return WorkforceEmployee::where('tenant_id', $tenantId)->get();
    }

    public function getById(int $employeeId): ?WorkforceEmployee
    {
        return WorkforceEmployee::find($employeeId);
    }

    public function getActive(int $tenantId, int $branchId): Collection
    {
        return WorkforceEmployee::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->get();
    }
}
