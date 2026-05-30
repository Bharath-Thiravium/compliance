<?php

namespace App\Services\Compliance\FormGenerator;

class ShopsFormVIGenerator extends BaseFormGenerator
{
    protected string $formCode = 'SHOPS_FORM_VI';
    protected string $view = 'compliance.forms.shops_form_vi';

    // Map raw attendance status to legend codes
    private function mapStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'present'          => 'W/D',
            'holiday'          => 'H',
            'absent', 'leave'  => 'N/E',
            default            => 'H',  // declared holiday with no attendance record = H
        };
    }

    protected function prepareData(array $rawData): array
    {
        $holidays             = $rawData['holidays'] ?? [];
        $employees            = $rawData['employees'] ?? [];
        $attendanceOnHolidays = $rawData['attendance_on_holidays'] ?? [];

        // Build ordered list of holiday dates (max 9 columns)
        $holidayDates = array_slice(array_column($holidays, 'holiday_date'), 0, 9);

        $rows = [];
        foreach ($employees as $emp) {
            $code = $emp['employee_code'] ?? '';
            $row  = [
                'employee_name' => $emp['name'] ?? 'N/A',
                'ticket'        => $emp['father_name'] ?? $code,
                'remarks'       => '',
            ];

            foreach (range(1, 9) as $i) {
                $date = $holidayDates[$i - 1] ?? null;
                if ($date && isset($attendanceOnHolidays[$code][$date])) {
                    $att = $attendanceOnHolidays[$code][$date];
                    $row['holiday' . $i] = $this->mapStatus($att['status'] ?? null);
                } else {
                    $row['holiday' . $i] = $date ? 'H' : '';
                }
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
