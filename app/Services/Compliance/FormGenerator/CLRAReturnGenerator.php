<?php

namespace App\Services\Compliance\FormGenerator;

class CLRAReturnGenerator extends BaseFormGenerator
{
    protected string $formCode = 'CLRA_RETURN';
    protected string $view = 'compliance.forms.clra_return';

    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] as $record) {
            $rows[] = [
                'contractor_name' => $record->contractor_name ?? 'N/A',
                'work_location' => $record->work_location ?? 'N/A',
                'total_workers' => $record->total_workers ?? 0,
                'total_wages' => round($record->total_wages ?? 0, 2),
                'period_from' => $record->period_from ?? 'N/A',
                'period_to' => $record->period_to ?? 'N/A',
            ];
        }

        return [
            'header' => [
                'form_title' => 'CLRA Return - Half-Yearly Return',
                'period' => $this->formatPeriod($rawData['period_month'], $rawData['period_year']),
                'branch' => $rawData['branch'] ?? [],
                'tenant' => $rawData['tenant'] ?? [],
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
