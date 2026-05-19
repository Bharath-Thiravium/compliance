<?php

namespace App\Services\Compliance;

use App\Models\ComplianceTimeline;
use App\Models\ComplianceFormsMaster;
use Carbon\Carbon;

class ComplianceTimelineService
{
    public function createTimelineOnBatchCreation(int $tenantId, int $periodMonth, int $periodYear): void
    {
        $forms = ComplianceFormsMaster::where('is_active', true)->get();
        $config = config('compliance_forms');

        foreach ($forms as $form) {
            $formConfig = $config[$form->form_code] ?? null;
            if (!$formConfig || !isset($formConfig['filing_frequency'])) {
                continue;
            }

            ComplianceTimeline::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'form_master_id' => $form->id,
                    'period_month' => $periodMonth,
                    'period_year' => $periodYear,
                ],
                [
                    'due_date' => $this->calculateDueDate($periodMonth, $periodYear, $formConfig['due_rule']),
                    'status' => 'Pending',
                    'reminder_sent' => false,
                ]
            );
        }
    }

    public function calculateDueDate(int $periodMonth, int $periodYear, string $dueRule): Carbon
    {
        $periodDate = Carbon::create($periodYear, $periodMonth, 1);

        return match ($dueRule) {
            'next_month_10' => $periodDate->copy()->addMonth()->day(10),
            'next_year_jan_31' => Carbon::create($periodYear + 1, 1, 31),
            'next_year_feb_15' => Carbon::create($periodYear + 1, 2, 15),
            'next_quarter_15' => $this->getNextQuarterDate($periodDate)->day(15),
            'next_half_year_30' => $this->getNextHalfYearDate($periodDate)->day(30),
            'same_day' => $periodDate,
            default => $periodDate->copy()->addMonth()->day(10),
        };
    }

    private function getNextQuarterDate(Carbon $date): Carbon
    {
        $quarter = ceil($date->month / 3);
        $nextQuarterMonth = ($quarter * 3) + 1;
        
        if ($nextQuarterMonth > 12) {
            return Carbon::create($date->year + 1, $nextQuarterMonth - 12, 1);
        }
        
        return Carbon::create($date->year, $nextQuarterMonth, 1);
    }

    private function getNextHalfYearDate(Carbon $date): Carbon
    {
        $half = $date->month <= 6 ? 1 : 2;
        $nextHalfMonth = $half === 1 ? 7 : 1;
        $nextYear = $half === 2 ? $date->year + 1 : $date->year;
        
        return Carbon::create($nextYear, $nextHalfMonth, 1);
    }

    public function updateOverdueStatuses(): int
    {
        return ComplianceTimeline::where('status', 'Pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'Overdue']);
    }

    public function markAsGenerated(int $tenantId, int $formMasterId, int $periodMonth, int $periodYear): void
    {
        ComplianceTimeline::where('tenant_id', $tenantId)
            ->where('form_master_id', $formMasterId)
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->update(['status' => 'Generated']);
    }

    public function markAsFiled(int $tenantId, int $formMasterId, int $periodMonth, int $periodYear): void
    {
        ComplianceTimeline::where('tenant_id', $tenantId)
            ->where('form_master_id', $formMasterId)
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->update(['status' => 'Filed']);
    }

    public function getTimelineMetrics(int $tenantId, ?int $periodMonth = null, ?int $periodYear = null): array
    {
        $query = ComplianceTimeline::where('tenant_id', $tenantId);

        if ($periodMonth && $periodYear) {
            $query->where('period_month', $periodMonth)
                  ->where('period_year', $periodYear);
        }

        $timelines = $query->get();

        return [
            'total' => $timelines->count(),
            'pending' => $timelines->where('status', 'Pending')->count(),
            'generated' => $timelines->where('status', 'Generated')->count(),
            'filed' => $timelines->where('status', 'Filed')->count(),
            'overdue' => $timelines->where('status', 'Overdue')->count(),
        ];
    }
}
