<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormAService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_A');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        $rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', $tenantId)
            ->where('e.branch_id', $branchId)
            ->select([
                'e.employee_code',
                'e.name as employee_name',
                DB::raw("'' as father_name"),
                DB::raw("'' as gender"),
                DB::raw("'' as permanent_address"),
                DB::raw("'' as nationality"),
                ''' as dob',
                DB::raw("'' as education_level"),
                DB::raw("'' as aadhaar"),
                'e.date_of_joining',
                'e.designation',
                DB::raw("'' as employment_type"),
                DB::raw("'' as mobile"),
                DB::raw("'' as bank_account"),
                DB::raw("'' as ifsc_code"),
                DB::raw("'' as uan"),
                'e.pf_number as esic_number',
                DB::raw("'' as aadhaar_linked"),
                DB::raw("'' as pan"),
                DB::raw("'' as category"),
                DB::raw("'' as present_address"),
                DB::raw("'' as identification_mark"),
            ])
            ->orderBy('e.employee_code')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_A', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_employees' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
