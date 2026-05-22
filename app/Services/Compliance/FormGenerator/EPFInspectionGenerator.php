<?php

namespace App\Services\Compliance\FormGenerator;

class EPFInspectionGenerator extends BaseFormGenerator
{
    protected string $formCode = 'EPF_INSPECTION';
    protected string $view     = 'compliance.forms.epf_inspection';

    protected function prepareData(array $rawData): array
    {
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code'   => $record['employee_code']  ?? '',
                'name'            => $record['name']           ?? '',
                'father_name'     => $record['father_name']    ?? '',
                'designation'     => $record['designation']    ?? '',
                'uan'             => $record['uan']            ?? $record['pf_number'] ?? '',
                'pf_number'       => $record['pf_number']      ?? '',
                'esi_number'      => $record['esi_number']     ?? '',
                'date_of_joining' => $record['date_of_joining'] ?? '',
                'basic_earned'    => (float) ($record['basic_earned']  ?? 0),
                'da_earned'       => (float) ($record['da_earned']     ?? 0),
                'gross_salary'    => (float) ($record['gross_salary']  ?? 0),
                'pf_employee'     => (float) ($record['pf_employee']   ?? 0),
                'pf_employer'     => (float) ($record['pf_employer']   ?? 0),
                'esi_employee'    => (float) ($record['esi_employee']  ?? 0),
                'total_days_worked' => $record['total_days_worked'] ?? 0,
                'net_salary'      => (float) ($record['net_salary']    ?? 0),
            ];
        }

        $totals = $this->calculateTotals($rows, [
            'basic_earned', 'da_earned', 'gross_salary',
            'pf_employee', 'pf_employer', 'esi_employee', 'net_salary',
        ]);

        return [
            'header' => [
                'form_title'         => 'EPF Inspection Register',
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'pf_code'            => $branch['pf_code']            ?? $tenant['pf_code'] ?? '',
                'factory_name'       => $branch['name']               ?? '',
                'address'            => $branch['address']            ?? '',
                'owner_name'         => $tenant['owner_name']         ?? $tenant['name'] ?? '',
                'period'             => $this->formatPeriod($month, $year),
                'branch'             => $branch,
                'tenant'             => $tenant,
            ],
            'rows'   => $rows,
            'totals' => $totals,
            'is_nil' => empty($rows),
        ];
    }
}
