<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\Log;

class FormXXIIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XXII';
    protected string $view     = 'compliance.forms.form_xxii';

    private function val($value): string
    {
        $v = trim((string) ($value ?? ''));
        return ($v !== '' && $v !== '0') ? $v : '';
    }

    protected function prepareData(array $rawData): array
    {
        $tenant  = $rawData['tenant'] ?? [];
        $branch  = $rawData['branch'] ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? date('Y');
        $records = $rawData['records'] ?? [];

        Log::info('FORM_XXII prepareData', ['raw_count' => count($records)]);

        $rows = [];
        $seen = [];

        foreach ($records as $record) {
            $record        = $this->normalizeRecord($record);
            $advanceAmount = (float) ($record['advance_amount'] ?? 0);
            if ($advanceAmount <= 0) continue;

            // Composite deduplication: same employee + same advance date + same amount
            $key = implode('|', [
                $record['employee_code']  ?? $record['employee_name'] ?? '',
                $record['advance_date']   ?? '',
                $advanceAmount,
            ]);

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $rows[] = [
                'name'                  => $this->val($record['employee_name'] ?? $record['name'] ?? null),
                'father_name'           => $this->val($record['father_name']   ?? null),
                'designation'           => $this->val($record['designation']   ?? null),
                'advance_date_amount_1' => $this->val($record['advance_date'] ?? null) . ($advanceAmount > 0 ? ' / ₹' . number_format($advanceAmount, 2) : ''),
                'advance_date_amount_2' => '',
                'purpose'               => $this->val($record['purpose']       ?? null),
                'installments'          => $this->val($record['installments']  ?? '1'),
                'installment_repaid'    => '',
                'last_installment_date' => '',
            ];
        }

        Log::info('FORM_XXII final rows', ['count' => count($rows)]);

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
            'all_nil_advances' => false,
        ];
    }
}
