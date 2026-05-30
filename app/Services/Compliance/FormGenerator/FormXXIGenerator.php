<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\Log;

class FormXXIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXI';
    protected string $view     = 'compliance.forms.form_xxi';

    protected function prepareData(array $rawData): array
    {
        $tenant  = $rawData['tenant'] ?? [];
        $branch  = $rawData['branch'] ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? date('Y');
        $period  = $rawData['period'] ?? \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        $records = $rawData['records'] ?? [];

        Log::info('FORM_XXI prepareData', ['raw_count' => count($records)]);

        $rows = [];
        $seen = [];

        foreach ($records as $record) {
            $record    = $this->normalizeRecord($record);
            $fineAmt   = (float) ($record['fine_amount'] ?? 0);
            if ($fineAmt <= 0) continue;

            // Composite deduplication: same employee + same offence date + same amount
            $key = implode('|', [
                $record['employee_code']   ?? $record['employee_name'] ?? '',
                $record['date_of_offence'] ?? '',
                $fineAmt,
            ]);

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $rows[] = [
                'name'            => $record['employee_name'] ?? '',
                'father_name'     => $record['father_name']   ?? '',
                'designation'     => $record['designation']   ?? '',
                'act_or_omission' => $record['act_or_omission'] ?? '',
                'date_of_offence' => $record['date_of_offence'] ?? '',
                'showed_cause'    => $record['showed_cause']    ?? '',
                'heard_by'        => $record['heard_by']        ?? '',
                'wage_period'     => $record['wage_period']     ?? '',
                'fine_amount'     => number_format($fineAmt, 2),
                'fine_realised'   => $record['fine_realised']   ?? '',
                'remarks'         => $record['remarks']         ?? '',
            ];
        }

        Log::info('FORM_XXI final rows', ['count' => count($rows)]);

        return [
            'header' => [
                'contractor_name'    => $tenant['name']    ?? '',
                'work_nature'        => $branch['address'] ?? '',
                'establishment_name' => $branch['name']    ?? '',
                'principal_employer' => $tenant['name']    ?? '',
                'month_year'         => $period,
            ],
            'rows'   => $rows,
            'is_nil' => empty($rows),
        ];
    }
}
