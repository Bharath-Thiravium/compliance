<?php

namespace App\Services\Compliance\FormGenerator;

use Illuminate\Support\Facades\Log;

class FormDGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_D';
    protected string $view = 'compliance.forms.form_d';

    protected function prepareData(array $rawData): array
    {
        try {
            Log::info("FormDGenerator: Starting prepareData", ['records_count' => count($rawData['records'] ?? [])]);

            $employees = $this->groupByEmployee($rawData['records'] ?? []);
            Log::info("FormDGenerator: Grouped employees", ['employee_count' => count($employees)]);

            $rows = [];
            $totals = [
                'total_present' => 0,
                'paid_holidays' => 0,
                'paid_leave' => 0,
                'weekly_off' => 0,
                'absent_days' => 0,
                'total_days' => 0,
            ];

            foreach ($employees as $employeeCode => $records) {
                try {
                    $row = $this->buildEmployeeRow($records);
                    $rows[] = $row;

                    $totals['total_present'] += $row['total_present'];
                    $totals['paid_holidays'] += $row['paid_holidays'];
                    $totals['paid_leave'] += $row['paid_leave'];
                    $totals['weekly_off'] += $row['weekly_off'];
                    $totals['absent_days'] += $row['absent_days'];
                    $totals['total_days'] += $row['total_days'];
                } catch (\Exception $e) {
                    Log::error("FormDGenerator: Error building row for employee {$employeeCode}", ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            Log::info("FormDGenerator: Built rows", ['row_count' => count($rows)]);

            $result = [
                'header' => [
                    'establishment_name' => $rawData['tenant']['name'] ?? '',
                    'owner_name'         => $rawData['tenant']['owner_name'] ?? '',
                    'tenant'             => $rawData['tenant'] ?? [],
                    'branch'             => $rawData['branch'] ?? [],
                ],
                'establishment_name' => $rawData['tenant']['name'] ?? '',
                'owner_name' => $rawData['tenant']['owner_name'] ?? '',
                'month_name' => $this->getMonthName($rawData['meta']['month'] ?? 1),
                'year' => $rawData['meta']['year'] ?? 2024,
                'rows' => $rows,
                'entries' => $rows,
                'totals' => $totals,
                'is_nil' => count($rows) === 0,
            ];

            Log::info("FormDGenerator: prepareData complete", ['is_nil' => $result['is_nil']]);
            return $result;
        } catch (\Exception $e) {
            Log::error("FormDGenerator: prepareData failed", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function groupByEmployee(array|\Illuminate\Support\Collection $records): array
    {
        if ($records instanceof \Illuminate\Support\Collection) {
            $records = $records->toArray();
        }
        $grouped = [];
        foreach ($records as $record) {
            $record = is_object($record) ? (array) $record : $record;
            $code = $record['employee_code'] ?? '';
            if (!isset($grouped[$code])) {
                $grouped[$code] = [];
            }
            $grouped[$code][] = $record;
        }
        return $grouped;
    }

    private function buildEmployeeRow(array $records): array
    {
        $row = [
            'employee_name' => $records[0]['name'] ?? '',
            'designation'   => $records[0]['designation'] ?? '',
            'remarks'       => '',
        ];

        // Default all 31 days to 'A'
        for ($day = 1; $day <= 31; $day++) {
            $row["day_{$day}"] = 'A';
        }

        $counts = [
            'present'    => 0,
            'holiday'    => 0,
            'leave'      => 0,
            'weekly_off' => 0,
            'absent'     => 0,
            'half_day'   => 0,
        ];

        foreach ($records as $record) {
            $date   = $record['attendance_date'] ?? '';
            $status = strtolower(trim($record['status'] ?? ''));

            if ($date) {
                try {
                    $day = (int) date('d', strtotime($date));
                    if ($day >= 1 && $day <= 31) {
                        $row["day_{$day}"] = $this->formatStatus($status);
                    }
                } catch (\Exception $e) {
                    Log::warning("FormDGenerator: Error parsing date {$date}", ['error' => $e->getMessage()]);
                }
            }

            // Map normalised status to count bucket
            $bucket = match($status) {
                'present', 'p'                          => 'present',
                'absent', 'a'                           => 'absent',
                'half_day', 'halfday', 'hd', 'half day' => 'half_day',
                'leave', 'pl', 'paid_leave'             => 'leave',
                'holiday', 'ph', 'paid_holiday'         => 'holiday',
                'weekly_off', 'weeklyoff', 'wo', 'w/o'  => 'weekly_off',
                default                                 => 'absent',
            };
            $counts[$bucket]++;
        }

        $presentDays = $counts['present'] + ($counts['half_day'] * 0.5);

        $row['total_present'] = $presentDays;
        $row['paid_holidays'] = $counts['holiday'];
        $row['paid_leave']    = $counts['leave'];
        $row['weekly_off']    = $counts['weekly_off'];
        $row['absent_days']   = $counts['absent'] + ($counts['half_day'] * 0.5);
        $row['total_days']    = $counts['present'] + $counts['half_day'] + $counts['leave']
                              + $counts['weekly_off'] + $counts['holiday'] + $counts['absent'];

        return $row;
    }

    private function formatStatus(string $status): string
    {
        return match(strtolower(trim($status))) {
            'present', 'p'                           => 'P',
            'absent', 'a'                            => 'A',
            'half_day', 'halfday', 'hd', 'half day'  => 'HD',
            'leave', 'pl', 'paid_leave'              => 'PL',
            'holiday', 'ph', 'paid_holiday'          => 'PH',
            'weekly_off', 'weeklyoff', 'wo', 'w/o'   => 'W/O',
            default                                  => 'A',
        };
    }

    public function generatePdf(array $formData): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($this->view, $formData)
            ->setPaper('A3', 'landscape')
            ->setOption('isHtml5ParserEnabled', false)
            ->setOption('isRemoteEnabled', false)
            ->setOption('dpi', 96)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('chroot', [public_path()]);

        return $pdf->output();
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        return $months[$month] ?? '';
    }
}
