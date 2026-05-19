<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form26Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_26');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('incident_documents as id')
            ->join('workforce_employee as e', 'e.id', '=', 'id.employee_id')
            ->where('id.tenant_id', $tenantId)
            ->where('id.branch_id', $branchId)
            ->whereBetween('id.incident_date', [$startDate, $endDate])
            ->where('id.incident_type', 'accident')
            ->select([
                DB::raw("'' as accident_no"),
                'id.incident_date as accident_date',
                'e.name as injured_person',
                DB::raw("'' as place_of_accident"),
                'id.description as accident_description',
                'id.severity as injury_nature',
                DB::raw("'' as form_18_date"),
                DB::raw("'' as return_to_work_date"),
                DB::raw("'' as return_report_date"),
                DB::raw("'' as subsequent_report_date"),
                DB::raw("0 as days_away"),
                DB::raw("0 as man_days_lost"),
                'id.action_taken as disablement_details',
                'id.status as remarks',
            ])
            ->orderBy('id.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_26', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_accidents' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
