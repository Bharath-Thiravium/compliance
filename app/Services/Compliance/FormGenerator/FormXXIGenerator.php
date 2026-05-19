<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXI';
    protected string $view = 'compliance.forms.form_xxi';

    protected function prepareData(array $rawData): array
    {
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');
        $period = $rawData['period'] ?? \Carbon\Carbon::create($year, $month, 1)->format('F Y');

        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $hasFine = !empty($record['fine_amount']) && $record['fine_amount'] > 0;

            $rows[] = [
                'name'            => $record['employee_name'] ?? '',
                'father_name'     => $record['father_name']   ?? '',
                'designation'     => $record['designation']   ?? '',
                'act_or_omission' => $hasFine ? ($record['act_or_omission'] ?? 'Misconduct') : 'NIL',
                'date_of_offence' => $hasFine ? ($record['date_of_offence'] ?? 'NIL') : 'NIL',
                'showed_cause'    => $hasFine ? ($record['showed_cause']    ?? 'NIL') : 'NIL',
                'heard_by'        => $hasFine ? ($record['heard_by']        ?? 'NIL') : 'NIL',
                'wage_period'     => $hasFine ? ($record['wage_period']     ?? 'NIL') : 'NIL',
                'fine_amount'     => $hasFine ? number_format((float)$record['fine_amount'], 2) : 'NIL',
                'fine_realised'   => $hasFine ? ($record['fine_realised']   ?? 'NIL') : 'NIL',
                'remarks'         => $hasFine ? ($record['remarks']         ?? '-')   : '-',
            ];
        }

        return [
            'header' => [
                'contractor_name'    => $tenant['name']    ?? '',
                'work_nature'        => $branch['address'] ?? '',
                'establishment_name' => $branch['name']    ?? '',
                'principal_employer' => $tenant['name']    ?? '',
                'month_year'         => $period,
            ],
            'rows'   => $rows,
            'is_nil' => count($rows) === 0,
        ];
    }
}
