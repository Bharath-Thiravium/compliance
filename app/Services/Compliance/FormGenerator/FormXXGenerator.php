<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XX';
    protected string $view     = 'compliance.forms.form_xx';

    private function val(mixed $value, string $fallback = '-'): string
    {
        $v = trim((string) ($value ?? ''));
        return $v !== '' ? $v : $fallback;
    }

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $tenant  = $rawData['tenant'] ?? [];
        $branch  = $rawData['branch'] ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($records as $record) {
            $record          = $this->normalizeRecord($record);
            $deductionAmount = (float) ($record['deduction_amount'] ?? 0);

            $rows[] = [
                'employee_code'      => $this->val($record['employee_code']      ?? ''),
                'employee_name'      => $this->val($record['employee_name']      ?? ''),
                'father_name'        => $this->val($record['father_name']        ?? ''),
                'designation'        => $this->val($record['designation']        ?? ''),
                'damage_particulars' => $this->val($record['damage_particulars'] ?? ''),
                'damage_date'        => $this->val($record['damage_date']        ?? ''),
                'showed_cause'       => $this->val($record['showed_cause']       ?? ''),
                'witness_name'       => $this->val($record['witness_name']       ?? ''),
                'deduction_amount'   => $deductionAmount > 0 ? number_format($deductionAmount, 2) : '-',
                'instalments'        => $this->val($record['instalments']        ?? ''),
                'first_month'        => $this->val($record['first_month']        ?? ''),
                'last_month'         => $this->val($record['last_month']         ?? ''),
                'remarks'            => $this->val($record['remarks']            ?? ''),
            ];
        }

        $totalDeductions = array_sum(array_map(
            fn($r) => is_numeric(str_replace(',', '', $r['deduction_amount'])) ? (float) str_replace(',', '', $r['deduction_amount']) : 0,
            $rows
        ));

        return [
            'header' => [
                'form_title'         => 'FORM XX - Register of Deductions for Damage or Loss',
                'period'             => $this->formatPeriod($month, $year),
                'contractor_name'    => $tenant['name']                ?? '',
                'work_nature'        => $branch['address']             ?? $branch['name'] ?? '',
                'establishment_name' => $branch['name']                ?? '',
                'principal_employer' => $tenant['establishment_name']  ?? $tenant['name'] ?? '',
            ],
            'rows'   => $rows,
            'totals' => ['deduction_amount' => $totalDeductions],
            'is_nil' => empty($rows),
        ];
    }
}
