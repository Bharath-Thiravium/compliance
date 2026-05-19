<?php

namespace App\Compliance\Builders;

class InspectionRegisterBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $incidents->map(fn($incident) => [
            'incident_date' => $incident->incident_date ?? 'N/A',
            'employee_name' => $incident->employee->name ?? 'N/A',
            'incident_type' => $incident->incident_type ?? 'N/A',
            'description' => $incident->description ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
