<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsFormVIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_VI';
    protected string $view = 'compliance.forms.shops_form_vi';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $rows = [];
        $employeeMap = [];

        // Group records by employee
        foreach ($records as $record) {
            $code = $record['employee_code'] ?? '';
            if (!isset($employeeMap[$code])) {
                $employeeMap[$code] = [
                    'employee_name' => $record['name'] ?? 'N/A',
                    'ticket'        => $record['father_name'] ?? $record['employee_code'] ?? '',
                    'holidays'      => [],
                ];
            }
            $employeeMap[$code]['holidays'][] = [
                'date' => $record['attendance_date'] ?? '',
                'status' => $record['status'] ?? '',
            ];
        }

        // Convert to rows with holiday columns
        foreach ($employeeMap as $employee) {
            $row = [
                'employee_name' => $employee['employee_name'],
                'ticket' => $employee['ticket'],
                'holiday1' => '',
                'holiday2' => '',
                'holiday3' => '',
                'holiday4' => '',
                'holiday5' => '',
                'holiday6' => '',
                'holiday7' => '',
                'holiday8' => '',
                'holiday9' => '',
                'remarks' => '',
            ];

            // Map holidays to columns (max 9)
            foreach ($employee['holidays'] as $idx => $holiday) {
                if ($idx >= 9) break;
                $row['holiday' . ($idx + 1)] = $holiday['status'] ?? '';
            }

            $rows[] = $row;
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'SHOPS FORM VI - Register of National and Festival Holidays',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'establishment_name' => $branch['name'] ?? 'N/A',
                'owner_name' => $tenant['owner_name'] ?? $tenant['name'] ?? 'N/A',
                'factory_name' => $branch['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
