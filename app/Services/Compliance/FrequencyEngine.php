<?php

namespace App\Services\Compliance;

use App\Models\ComplianceFormsMaster;
use Illuminate\Support\Collection;

class FrequencyEngine
{
    /**
     * Get applicable forms for a given month
     */
    public function getApplicableForms(int $month, ?int $tenantId = null): Collection
    {
        $forms = ComplianceFormsMaster::where('is_active', true)->get();
        
        return $forms->filter(fn($form) => $this->isApplicable($form->frequency, $month));
    }

    /**
     * Check if a form's frequency matches the given month
     */
    private function isApplicable(string $frequency, int $month): bool
    {
        $freq = strtolower(trim($frequency));

        return match ($freq) {
            'monthly' => true,
            'quarterly' => in_array($month, [3, 6, 9, 12]),
            'half-yearly', 'halfyearly', 'half yearly' => in_array($month, [6, 12]),
            'yearly', 'annual', 'annually' => $month === 12,
            'event' => false,
            default => false,
        };
    }

    /**
     * Get frequency label for display
     */
    public function getFrequencyLabel(string $frequency): string
    {
        return match (strtolower(trim($frequency))) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'half-yearly', 'halfyearly', 'half yearly' => 'Half-Yearly',
            'yearly', 'annual', 'annually' => 'Yearly',
            'event' => 'Event-based',
            default => 'Unknown',
        };
    }
}
