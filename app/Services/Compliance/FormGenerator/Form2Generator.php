<?php

namespace App\Services\Compliance\FormGenerator;

class Form2Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_2';
    protected string $view     = 'compliance.forms.form_2';

    protected function prepareData(array $rawData): array
    {
        $tenant  = $rawData['tenant']  ?? [];
        $branch  = $rawData['branch']  ?? [];
        $factory = $rawData['factory_details'] ?? [];
        $month   = $rawData['meta']['month'] ?? 1;
        $year    = $rawData['meta']['year']  ?? date('Y');

        $header = [
            'form_title'           => 'FORM 2 - Notice of Periods of Work',
            'period'               => $this->formatPeriod($month, $year),
            'factory_name'         => $factory['factory_name'] ?? $branch['name'] ?? '',
            'place'                => $factory['place'] ?? '',
            'district'             => $factory['district'] ?? '',
            'date_first_exhibited' => $factory['date_first_exhibited'] ?? '',
            'branch'               => $branch,
            'tenant'               => $tenant,
        ];

        $records = $rawData['records'] ?? [];

        // Filter to rows that have a usable shift_name
        $withShift = array_filter($records, fn($r) => !empty(trim((string)($r['shift_name'] ?? ''))));

        if (empty($withShift)) {
            return [
                'header'       => $header,
                'relay_groups' => [],
                'rows'         => [],
                'totals'       => [],
                'is_nil'       => true,
                'nil_message'  => 'No Shift Schedule Data Available',
            ];
        }

        // Group by shift_name|shift_start|shift_end
        $groups = [];
        foreach ($withShift as $r) {
            $shiftName  = trim((string)($r['shift_name']  ?? ''));
            $shiftStart = trim((string)($r['shift_start'] ?? ''));
            $shiftEnd   = trim((string)($r['shift_end']   ?? ''));
            $key        = $shiftName . '|' . $shiftStart . '|' . $shiftEnd;

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'shift_name'       => $shiftName,
                    'shift_start'      => $shiftStart,
                    'shift_end'        => $shiftEnd,
                    'men'              => 0,
                    'women'            => 0,
                    'children'         => 0,
                    'weekly_off_days'  => 0,
                    'holiday_days'     => 0,
                    'attendance_dates' => [],
                ];
            }

            $gender = strtolower(trim((string)($r['gender'] ?? '')));
            if (in_array($gender, ['male', 'm'], true)) {
                $groups[$key]['men']++;
            } elseif (in_array($gender, ['female', 'f'], true)) {
                $groups[$key]['women']++;
            } elseif (in_array($gender, ['child', 'male_child', 'female_child'], true)) {
                $groups[$key]['children']++;
            }

            if (!empty($r['weekly_off'])) {
                $groups[$key]['weekly_off_days']++;
            }
            if (!empty($r['holiday_flag'])) {
                $groups[$key]['holiday_days']++;
            }

            $date = (string)($r['attendance_date'] ?? '');
            if ($date && !in_array($date, $groups[$key]['attendance_dates'], true)) {
                $groups[$key]['attendance_dates'][] = $date;
            }
        }

        $relayGroups = array_values($groups);

        return [
            'header'       => $header,
            'relay_groups' => $relayGroups,
            'rows'         => $relayGroups,
            'totals'       => [
                'men'      => array_sum(array_column($relayGroups, 'men')),
                'women'    => array_sum(array_column($relayGroups, 'women')),
                'children' => array_sum(array_column($relayGroups, 'children')),
            ],
            'is_nil' => false,
        ];
    }
}
