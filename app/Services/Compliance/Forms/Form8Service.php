<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;
use App\Services\Compliance\Debug\FormDebugger;

class Form8Service extends BaseFormService
{
    public function generate(int $tenantId, int $branchId, int $month, int $year): array
    {
        FormDebugger::start('FORM_8');

        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
        $this->month = $month;
        $this->year = $year;

        [$startDate, $endDate] = $this->getDateRange();

        $rows = DB::table('incident_documents as id')
            ->join('workforce_employee as e', 'e.id', '=', 'id.employee_id')
            ->where('id.tenant_id', $tenantId)
            ->where('id.branch_id', $branchId)
            ->where('id.incident_type', 'accident')
            ->whereBetween('id.incident_date', [$startDate, $endDate])
            ->select([
                'e.name as employee_name',
                'id.incident_date',
                'id.description',
                'id.severity',
                'id.action_taken',
            ])
            ->orderBy('id.incident_date')
            ->get()
            ->map(fn($row) => (array)$row)
            ->toArray();

        FormDebugger::end('FORM_8', $rows);

        if (empty($rows)) {
            return $this->nilResponse();
        }

        $totals = [
            'total_accidents' => count($rows),
        ];

        return $this->buildResponse($rows, $totals);
    }
}
