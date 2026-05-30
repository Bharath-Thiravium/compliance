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

            $month   = $rawData['meta']['month'] ?? 1;
            $year    = $rawData['meta']['year']  ?? date('Y');

            $employees = $this->groupByEmployee($rawData['records'] ?? []);
            Log::info('FORM D GROUPED', [
                'employee_count' => count($employees),
                'keys'           => array_keys($employees),
            ]);

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $rows = [];
            $totals = [
                'total_present' => 0,
                'paid_holidays' => 0,
                'paid_leave'    => 0,
                'weekly_off'    => 0,
                'absent_days'   => 0,
                'total_days'    => 0,
            ];

            foreach ($employees as $employeeKey => $records) {
                try {
                    $row = $this->buildEmployeeRow($records, $month, $year);
                    $rows[] = $row;

                    $totals['total_present'] += $row['total_present'];
                    $totals['paid_holidays'] += $row['paid_holidays'];
                    $totals['paid_leave']    += $row['paid_leave'];
                    $totals['weekly_off']    += $row['weekly_off'];
                    $totals['absent_days']   += $row['absent_days'];
                    $totals['total_days']    += $row['total_days'];
                } catch (\Exception $e) {
                    Log::error("FormDGenerator: Error building row for employee key {$employeeKey}", ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            Log::info('FORM D FINAL', [
                'row_count' => count($rows),
                'totals'    => $totals,
                'sample'    => array_map(fn($r) => [
                    'name'          => $r['employee_name'],
                    'total_present' => $r['total_present'],
                    'total_days'    => $r['total_days'],
                ], array_slice($rows, 0, 3)),
            ]);

            Log::info("FormDGenerator: Built rows", ['row_count' => count($rows)]);

            $result = [
                'header' => [
                    'establishment_name' => $rawData['tenant']['name'] ?? '',
                    'owner_name'         => $rawData['tenant']['owner_name'] ?? $rawData['tenant']['name'] ?? '',
                    'tenant'             => $rawData['tenant'] ?? [],
                    'branch'             => $rawData['branch'] ?? [],
                    'period'             => $this->formatPeriod($rawData['meta']['month'] ?? 1, $rawData['meta']['year'] ?? date('Y')),
                ],
                'establishment_name' => $rawData['tenant']['name'] ?? '',
                'owner_name'         => $rawData['tenant']['owner_name'] ?? $rawData['tenant']['name'] ?? '',
                'month_name'         => $this->getMonthName($rawData['meta']['month'] ?? 1),
                'year'               => $rawData['meta']['year'] ?? 2024,
                'rows'               => $rows,
                'entries'            => $rows,
                'totals'             => $totals,
                'is_nil'             => count($rows) === 0,
            ];

            Log::info("FormDGenerator: prepareData complete", ['is_nil' => $result['is_nil']]);
            return $result;
        } catch (\Exception $e) {
            Log::error("FormDGenerator: prepareData failed", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function groupByEmployee(array $records): array
    {
        $grouped = [];
        foreach ($records as $record) {
            $record = is_object($record) ? (array) $record : $record;
            // Group by employee_id (stable integer PK) — employee_code may be null/empty
            // which would collapse all null-code employees into one bucket
            $key = $record['employee_id'] ?? $record['employee_code'] ?? '';
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $record;
        }
        return $grouped;
    }

    private function buildEmployeeRow(array $records, int $month, int $year): array
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

        // Track resolved status per day number to handle duplicate rows
        $seenDates = [];

        foreach ($records as $record) {
            $dateStr     = $record['attendance_date'] ?? '';
            $status      = strtolower(trim($record['status'] ?? ''));
            $weeklyOff   = !empty($record['weekly_off'])   && $record['weekly_off']   != '0';
            $holidayFlag = !empty($record['holiday_flag']) && $record['holiday_flag'] != '0';
            $leaveType   = !empty($record['leave_type'])   ? $record['leave_type'] : null;

            if (!$dateStr) continue;

            // Dedicated boolean columns take priority over status string
            if ($weeklyOff) {
                $resolved = 'weekly_off';
            } elseif ($holidayFlag) {
                $resolved = 'holiday';
            } elseif ($leaveType) {
                $resolved = 'leave';
            } else {
                $resolved = match($status) {
                    'present', 'p'                          => 'present',
                    'absent', 'a'                           => 'absent',
                    'half_day', 'halfday', 'hd', 'half day' => 'half_day',
                    'leave', 'pl', 'paid_leave'             => 'leave',
                    'holiday', 'ph', 'paid_holiday'         => 'holiday',
                    'weekly_off', 'weeklyoff', 'wo', 'w/o'  => 'weekly_off',
                    default                                 => 'absent',
                };
            }

            try {
                $day = (int) \Carbon\Carbon::parse($dateStr)->format('d');
                if ($day < 1 || $day > 31) continue;

                // Keep higher-priority status if same day appears twice
                if (isset($seenDates[$day])) {
                    $priority = ['present'=>6,'half_day'=>5,'leave'=>4,'holiday'=>3,'weekly_off'=>2,'absent'=>1];
                    if (($priority[$resolved] ?? 1) <= ($priority[$seenDates[$day]] ?? 1)) {
                        continue;
                    }
                }

                $seenDates[$day]   = $resolved;
                $row["day_{$day}"] = $this->formatStatus($resolved);
            } catch (\Exception $e) {
                Log::warning("FormDGenerator: Cannot parse date '{$dateStr}'", ['error' => $e->getMessage()]);
            }
        }

        // Count resolved statuses from the day map
        foreach ($seenDates as $resolvedStatus) {
            $counts[$resolvedStatus] = ($counts[$resolvedStatus] ?? 0) + 1;
        }

        // Days in the actual calendar month that have NO attendance record count as absent
        $daysInMonth  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $recordedDays = count($seenDates);
        $unrecordedDays = max(0, $daysInMonth - $recordedDays);
        $counts['absent'] += $unrecordedDays;

        $presentDays = $counts['present'] + ($counts['half_day'] * 0.5);

        $row['total_present'] = $presentDays;
        $row['paid_holidays'] = $counts['holiday'];
        $row['paid_leave']    = $counts['leave'];
        $row['weekly_off']    = $counts['weekly_off'];
        $row['absent_days']   = $counts['absent'] + ($counts['half_day'] * 0.5);
        $row['total_days']    = $daysInMonth;

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
