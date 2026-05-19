<?php

namespace App\Compliance\Builders;

class DangerousOccurrenceBuilder extends BaseBuilder
{
    protected function getData(): array
    {
        $incidents = $this->incidentRepo->getByBranchAndPeriod($this->tenantId, $this->branchId, $this->month, $this->year);
        
        if ($incidents->isEmpty()) {
            return ['status' => 'NIL'];
        }

        $mapped = $incidents->map(fn($incident) => [
            'sl_no' => $incident->id,
            'occurrence_date' => $incident->incident_date ?? 'N/A',
            'place' => $incident->location ?? 'N/A',
            'description' => $incident->description ?? 'N/A',
            'damage' => $incident->damage ?? 'N/A',
        ])->toArray();

        return [
            'period' => "{$this->month}/{$this->year}",
            'rows' => $mapped,
            'entries' => $mapped,
        ];
    }
}
