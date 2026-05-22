<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class FormCGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FormC';
    protected string $view = 'compliance.forms.form_c';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $rows = [];

        foreach ($records as $record) {
            $record = $this->normalizeRecord($record);

            $rows[] = [
                'employee_name'  => $this->str($record['employee_name'] ?? ''),
                'recovery_type'  => $this->str($record['recovery_type'] ?? ''),
                'particulars'    => $this->str($record['particulars'] ?? ''),
                'damage_date'    => $this->date($record['damage_date'] ?? ''),
                'amount'         => $this->num($record['amount'] ?? 0),
                'show_cause'     => $this->str($record['show_cause'] ?? ''),
                'explanation'    => $this->str($record['explanation'] ?? ''),
                'installments'   => $this->num($record['installments'] ?? 0),
                'first_month'    => $this->monthYear($record['first_month'] ?? ''),
                'last_month'     => $this->monthYear($record['last_month'] ?? ''),
                'recovery_date'  => $this->date($record['recovery_date'] ?? ''),
                'remarks'        => $this->str($record['remarks'] ?? ''),
            ];
        }

        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? 'Nil',
                'owner_name'         => $tenant['owner_name'] ?? $tenant['name'] ?? 'Nil',
                'period'             => $this->formatPeriod($month, $year),
            ],
            'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? 'Nil',
            'owner_name'         => $tenant['owner_name'] ?? $tenant['name'] ?? 'Nil',
            'period'             => $this->formatPeriod($month, $year),
            'rows'               => $rows,
            'is_nil'             => count($rows) === 0,
        ];
    }

    private function str(mixed $val): string
    {
        $v = trim((string)$val);
        return $v !== '' ? $v : 'Nil';
    }

    private function num(mixed $val): string
    {
        $v = (float)$val;
        return $v > 0 ? number_format($v, 2) : 'Nil';
    }

    private function date(mixed $val): string
    {
        if (empty($val)) return 'Nil';
        try {
            return Carbon::parse($val)->format('d/m/Y');
        } catch (\Exception $e) {
            return (string)$val;
        }
    }

    private function monthYear(mixed $val): string
    {
        if (empty($val)) return 'Nil';
        try {
            return Carbon::parse($val)->format('m/Y');
        } catch (\Exception $e) {
            return (string)$val;
        }
    }
}
