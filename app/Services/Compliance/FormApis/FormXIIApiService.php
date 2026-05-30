<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

class FormXIIApiService extends BaseFormApiService
{
    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        $rows = DB::table('contractor_master as cm')
            ->where('cm.tenant_id', $tenantId)
            ->where('cm.branch_id', $branchId)
            ->whereNull('cm.deleted_at')
            ->select([
                'cm.id',
                DB::raw("COALESCE(cm.contractor_name, cm.company_name, 'N/A') as contractor_name"),
                DB::raw("COALESCE(cm.contractor_code, '') as contractor_code"),
                DB::raw("COALESCE(cm.address, cm.company_address, 'N/A') as address"),
                DB::raw("COALESCE(cm.phone, cm.contact_number, '') as phone"),
                DB::raw("COALESCE(cm.email, '') as email"),
                DB::raw("COALESCE(cm.license_number, cm.license_no, '') as license_no"),
                DB::raw("COALESCE(cm.valid_to, cm.license_expiry, NULL) as license_expiry"),
            ])
            ->orderBy('cm.contractor_name')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        return [
            'records' => $rows,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }
}
