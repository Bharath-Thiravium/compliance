<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form17Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_17');

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
            ->where('id.incident_type', 'health')
            ->select([
                DB::raw("'' as works_no"),
                'e.name as employee_name',
                DB::raw("'' as sex"),
                DB::raw("0 as age"),
                'id.incident_date as employment_date',
                DB::raw("'' as leaving_date"),
                'id.description as reason',
                DB::raw("'' as nature_of_job"),
                DB::raw("'' as raw_material"),
                'id.action_taken as medical_examination',
                DB::raw("'' as suspension_period"),
                DB::raw("'' as recertified_date"),
                DB::raw("'' as unfitness_certificate"),
                DB::raw("'' as surgeon_signature"),
            ])
            ->orderBy('id.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_17', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_incidents' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
