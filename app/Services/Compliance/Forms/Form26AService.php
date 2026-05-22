<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form26AService extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_26A');

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
            ->where('id.incident_type', 'dangerous_occurrence')
            ->select([
                'e.name as employee_name',
                'id.incident_date',
                'id.description',
                'id.action_taken',
                'id.status',
            ])
            ->orderBy('id.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_26A', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_occurrences' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
