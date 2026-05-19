<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form11Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_11');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('incidents as i')
            ->join('workforce_employee as e', 'e.id', '=', 'i.employee_id')
            ->where('i.tenant_id', $tenantId)
            ->where('i.branch_id', $branchId)
            ->whereYear('i.incident_date', $year)
            ->whereMonth('i.incident_date', $month)
            ->select([
                DB::raw("DATE_FORMAT(i.incident_date, '%Y-%m-%d') as date_of_notice"),
                DB::raw("'' as time_of_notice"),
                'e.name as injured_person',
                DB::raw("'' as sex"),
                DB::raw("0 as age"),
                DB::raw("'' as insurance_no"),
                DB::raw("'' as occupation"),
                'i.cause',
                DB::raw("'' as nature"),
                'i.incident_date as injury_date',
                DB::raw("'' as injury_time"),
                'i.location as place',
                'i.activity',
                'i.first_aid_by as first_aid_person',
                DB::raw("'' as signature"),
                'i.witness as witnesses',
                'i.remarks',
            ])
            ->orderBy('i.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_11', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        return $this->buildResponse($rows);
    }
}
