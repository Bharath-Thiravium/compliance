<?php

namespace App\Services\Compliance\FormGenerator;

class FORMDERGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_D_ER';
    protected string $view = 'compliance.forms.form_d_er';

    protected function prepareData(array $rawData): array
    {
        $records = $rawData['records'] ?? [];
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];
        $month  = $rawData['meta']['month'] ?? 1;
        $year   = $rawData['meta']['year']  ?? date('Y');

        // Group by designation
        $grouped = [];
        foreach ($records as $record) {
            $designation = $record['designation'] ?? 'Unknown';
            $grouped[$designation][] = $record;
        }

        $rows       = [];
        $totalMen   = 0;
        $totalWomen = 0;

        foreach ($grouped as $designation => $employees) {
            $menCount   = 0;
            $womenCount = 0;
            $sumGross   = 0;
            $sumBasic   = 0;
            $sumDa      = 0;
            $sumHra     = 0;
            $sumOther   = 0;
            $count      = count($employees);

            foreach ($employees as $emp) {
                $gender = strtolower($emp['gender'] ?? '');
                if (in_array($gender, ['male', 'm'])) {
                    $menCount++;
                } elseif (in_array($gender, ['female', 'f'])) {
                    $womenCount++;
                }
                $sumGross += (float)($emp['gross_salary']    ?? 0);
                $sumBasic += (float)($emp['basic_earned']    ?? 0);
                $sumDa    += (float)($emp['da_earned']       ?? 0);
                $sumHra   += (float)($emp['hra_earned']      ?? 0);
                $sumOther += (float)($emp['other_allowances'] ?? 0);
            }

            $avg = fn(float $sum) => $count > 0 ? round($sum / $count, 2) : 0;

            $rows[] = [
                'category'         => $designation,
                'description'      => $designation,
                'men_count'        => $menCount,
                'women_count'      => $womenCount,
                'rate_remuneration'=> $avg($sumGross),
                'basic_wage'       => $avg($sumBasic),
                'da'               => $avg($sumDa),
                'hra'              => $avg($sumHra),
                'other_allowance'  => $avg($sumOther),
                'cash_value'       => 0,
            ];

            $totalMen   += $menCount;
            $totalWomen += $womenCount;
        }

        $period = $this->formatPeriod($month, $year);

        // Return root-level keys matching Blade variables directly.
        // Orchestrator merges header[] into root, so we also keep header[]
        // for consistency — but all Blade-required vars are at root level too.
        return [
            'header' => [
                'form_title'         => 'FORM D - Equal Remuneration Register',
                'period'             => $period,
                'establishment_name' => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'company_name'       => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
                'contractor_name'    => $tenant['name'] ?? '',
                'work_location'      => $branch['address'] ?? $branch['name'] ?? '',
                'principal_employer' => $tenant['name'] ?? '',
                'total_workers'      => $totalMen + $totalWomen,
                'total_men'          => $totalMen,
                'total_women'        => $totalWomen,
                'tenant'             => $tenant,
                'branch'             => $branch,
            ],
            // Root-level vars the Blade reads directly
            'company_name'      => $tenant['establishment_name'] ?? $tenant['name'] ?? '',
            'contractor_name'   => $tenant['name'] ?? '',
            'work_location'     => $branch['address'] ?? $branch['name'] ?? '',
            'principal_employer'=> $tenant['name'] ?? '',
            'total_workers'     => $totalMen + $totalWomen,
            'total_men'         => $totalMen,
            'total_women'       => $totalWomen,
            'month'             => \Carbon\Carbon::create($year, $month, 1)->format('F'),
            'year'              => $year,
            'rows'              => $rows,
            'totals'            => [],
            'is_nil'            => count($rows) === 0,
        ];
    }
}
