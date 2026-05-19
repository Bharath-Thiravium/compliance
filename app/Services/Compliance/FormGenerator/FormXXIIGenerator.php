<?php

namespace App\Services\Compliance\FormGenerator;

class FormXXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXII';
    protected string $view     = 'compliance.forms.form_xxii';

    private function val($value, string $fallback = 'NIL'): string
    {
        $v = trim((string) ($value ?? ''));
        return ($v !== '' && $v !== '0') ? $v : $fallback;
    }

    protected function prepareData(array $rawData): array
    {
        $rows   = [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        foreach ($rawData['records'] ?? [] as $record) {
            $record        = $this->normalizeRecord($record);
            $advanceAmount = (float) ($record['advance_amount'] ?? 0);

            $rows[] = [
                'name'                  => $this->val($record['employee_name'] ?? $record['name'] ?? null),
                'father_name'           => $this->val($record['father_name']   ?? null),
                'designation'           => $this->val($record['designation']   ?? null),
                'advance_date_amount_1' => $advanceAmount > 0
                    ? $this->val($record['advance_date'] ?? null) . ' / ₹' . number_format($advanceAmount, 2)
                    : 'NIL',
                'advance_date_amount_2' => 'NIL',
                'purpose'               => $this->val($record['purpose']       ?? null),
                'installments'          => $advanceAmount > 0 ? $this->val($record['installments'] ?? '1') : 'NIL',
                'installment_repaid'    => 'NIL',
                'last_installment_date' => 'NIL',
            ];
        }

        $allNil = !empty($rows) && collect($rows)->every(fn($r) => $r['advance_date_amount_1'] === 'NIL');

        return [
            'header' => [
                'contractor_name'    => $tenant['name']    ?? '',
                'work_nature'        => $branch['address'] ?? '',
                'establishment_name' => $branch['name']    ?? '',
                'principal_employer' => $tenant['name']    ?? '',
                'month_year'         => $this->formatPeriod($month, $year),
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            'rows'             => $rows,
            'is_nil'           => empty($rows),
            'all_nil_advances' => $allNil,
        ];
    }
}
