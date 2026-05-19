<?php

namespace App\Compliance\Builders;

class IncidentBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);

        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $incidents->map(fn($incident) => [
            'employee_code' => $incident->employee->employee_code ?? 'N/A',
            'employee_name' => $incident->employee->name ?? 'N/A',
            'designation' => $incident->employee->designation ?? 'N/A',
            'incident_date' => $incident->incident_date ?? 'N/A',
            'incident_type' => $incident->incident_type ?? 'N/A',
            'location' => $incident->location ?? 'N/A',
            'description' => $incident->description ?? 'N/A',
            'esi_number' => $incident->employee->esi_number ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
            'total_incidents' => $incidents->count(),
        ];
    }
}
