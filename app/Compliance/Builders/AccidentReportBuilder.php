<?php

namespace App\Compliance\Builders;

class AccidentReportBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $incident = $incidents->first();
        
        return [
            'registration_number' => 'N/A',
            'factory_name' => 'N/A',
            'employee_name' => $incident->employee->name ?? 'N/A',
            'incident_date' => $incident->incident_date ?? 'N/A',
            'description' => $incident->description ?? 'N/A',
            'injury_type' => $incident->incident_type ?? 'N/A',
        ];
    }
}
