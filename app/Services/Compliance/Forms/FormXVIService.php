<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class FormXVIService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_XVI');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        // Get unique employees deployed during the period
        $employees = DB::table('contract_labour_deployment as cl')
            ->leftJoin('workforce_employee as e', 'e.id', '=', 'cl.employee_id')
            ->where('cl.tenant_id', $tenantId)
            ->where('cl.branch_id', $branchId)
            ->whereBetween('cl.deployment_start', [$startDate, $endDate])
            ->select(
                'e.id',
                DB::raw("COALESCE(e.name, '') as name"),
                DB::raw("COALESCE(e.father_name, '') as father_name"),
                DB::raw("COALESCE(e.gender, '') as sex")
            )
            ->distinct()
            ->get();

        // Build rows with attendance data
        $rows = [];
        foreach ($employees as $emp) {
            $row = [
                'name' => $emp->name,
                'father_name' => $emp->father_name,
                'sex' => $emp->sex,
            ];

            // Get attendance for each day of the month
            for ($day = 1; $day <= 31; $day++) {
                $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                
                $attendance = DB::table('workforce_attendance')
                    ->where('employee_id', $emp->id)
                    ->where('attendance_date', $date)
                    ->where('tenant_id', $tenantId)
                    ->where('branch_id', $branchId)
                    ->first();

                // Map attendance status: P=Present, A=Absent, L=Leave, H=Holiday, etc.
                $row["day_$day"] = $attendance ? ($attendance->status ?? '') : '';
            }

            $row['remarks'] = '';
            $rows[] = $row;
        }

        FormDebugger::end('FORM_XVI', $rows);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        $branch = DB::table('branches')->where('id', $branchId)->where('tenant_id', $tenantId)->first();
        
        return [
            'contractor_name' => $tenant?->name ?? $tenant?->establishment_name ?? 'NIL',
            'establishment_name' => $branch?->branch_name ?? $branch?->unit_name ?? 'NIL',
            'principal_employer' => $tenant?->name ?? $tenant?->establishment_name ?? 'NIL',
            'work_nature' => $branch?->address ?? 'NIL',
            'work_location' => $branch?->address ?? 'NIL',
            'wage_period' => 'Monthly',
            'rows' => $rows,
            'header' => [
                'tenant' => [
                    'name' => $tenant?->name ?? $tenant?->establishment_name ?? 'NIL',
                    'address' => $tenant?->address ?? 'NIL',
                ],
                'branch' => [
                    'name' => $branch?->branch_name ?? $branch?->unit_name ?? 'NIL',
                    'address' => $branch?->address ?? 'NIL',
                ]
            ],
            'totals' => []
        ];
    }
}
