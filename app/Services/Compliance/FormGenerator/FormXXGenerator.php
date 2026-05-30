<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\Log;

class FormXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XX';
    protected string $view     = 'compliance.forms.form_xx';

    private function val(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $tenant  = $rawData['tenant'] ?? [];
        $branch  = $rawData['branch'] ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? date('Y');

        Log::info('FORM_XX prepareData', ['raw_count' => count($records)]);

        $rows = [];
        $seen = [];

        foreach ($records as $record) {
            $record          = $this->normalizeRecord($record);
            $deductionAmount = (float) ($record['deduction_amount'] ?? 0);
            if ($deductionAmount <= 0) continue;

            // Composite deduplication: same employee + same date + same amount = duplicate
            $key = implode('|', [
                $record['employee_code'] ?? '',
                $record['damage_date']   ?? '',
                $deductionAmount,
            ]);

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $rows[] = [
                'employee_code'      => $this->val($record['employee_code']      ?? ''),
                'employee_name'      => $this->val($record['employee_name']      ?? ''),
                'father_name'        => $this->val($record['father_name']        ?? ''),
                'designation'        => $this->val($record['designation']        ?? ''),
                'damage_particulars' => $this->val($record['damage_particulars'] ?? ''),
                'damage_date'        => $this->val($record['damage_date']        ?? ''),
                'showed_cause'       => $this->val($record['showed_cause']       ?? ''),
                'witness_name'       => $this->val($record['witness_name']       ?? ''),
                'deduction_amount'   => number_format($deductionAmount, 2),
                'instalments'        => $this->val($record['instalments']        ?? ''),
                'first_month'        => $this->val($record['first_month']        ?? ''),
                'last_month'         => $this->val($record['last_month']         ?? ''),
                'remarks'            => $this->val($record['remarks']            ?? ''),
            ];
        }

        Log::info('FORM_XX final rows', ['count' => count($rows)]);

        $totalDeductions = array_sum(array_map(
            fn($r) => (float) str_replace(',', '', $r['deduction_amount']),
            $rows
        ));

        return [
            'header' => [
                'form_title'         => 'FORM XX - Register of Deductions for Damage or Loss',
                'period'             => $this->formatPeriod($month, $year),
                'contractor_name'    => $tenant['name']               ?? '',
                'work_nature'        => $branch['address']            ?? $branch['name'] ?? '',
                'establishment_name' => $branch['name']               ?? '',
                'principal_employer' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
            ],
            'rows'   => $rows,
            'totals' => ['deduction_amount' => $totalDeductions],
            'is_nil' => empty($rows),
        ];
    }
}
