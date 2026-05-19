<?php

namespace App\Services\Compliance\FormGenerator;

use Carbon\Carbon;

class FormXIVGenerator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_XIV';
    protected string $view = 'compliance.forms.form_xiv';

    protected function prepareData(array $rawData): array
    {
        $tenant  = $rawData['tenant'] ?? [];
        $branch  = $rawData['branch'] ?? [];
        $period  = $rawData['period'] ?? '';
        $records = $rawData['records'] ?? [];

        $tenantName      = is_array($tenant) ? ($tenant['name'] ?? '') : (string) $tenant;
        $establishName   = is_array($tenant) ? ($tenant['establishment_name'] ?? $tenantName) : $tenantName;
        $branchUnitName  = is_array($branch) ? ($branch['name'] ?? '') : (string) $branch;
        $branchAddr      = is_array($branch) ? ($branch['address'] ?? '') : '';

        $cards = [];
        foreach ($records as $i => $record) {
            $record = $this->normalizeRecord($record);

            $doj = $record['date_of_joining'] ?? '';
            if ($doj && $doj !== '') {
                try { $doj = Carbon::parse($doj)->format('d/m/Y'); } catch (\Exception $e) {}
            }

            $wageRaw  = $record['wage_rate'] ?? '';
            $wageRate = ($wageRaw !== '' && (float)$wageRaw > 0)
                ? number_format((float)$wageRaw, 0) . '/-'
                : '-';

            // Contractor: use contractor_master name if available, else tenant name
            $contractorName = ($record['contractor_name'] !== '')
                ? $record['contractor_name']
                : $tenantName;

            $cards[] = [
                'establishment_name' => $branchUnitName ?: $establishName,
                'contractor_name'    => $contractorName,
                'work_location'      => $branchAddr,
                'principal_employer' => $tenantName,
                'workman_name'       => $record['employee_name']  ?? '-',
                'register_serial'    => $record['employee_code']  ?? ($i + 1),
                'designation'        => strtoupper($record['designation'] ?: ($record['work_description'] ?? '-')),
                'wage_rate'          => $wageRate,
                'wage_period'        => '(Monthly Wage)',
                'tenure'             => $doj ?: '-',
                'remarks'            => '-',
                'seal_path'          => $tenant['seal_path']      ?? null,
                'signature_path'     => $tenant['signature_path'] ?? null,
            ];
        }

        return [
            'header' => [
                'establishment_name' => $branchUnitName ?: $establishName,
                'branch'             => is_array($branch) ? $branch : [],
                'tenant'             => is_array($tenant) ? $tenant : [],
            ],
            'cards'  => $cards,
            'rows'   => [],
            'is_nil' => count($cards) === 0,
        ];
    }
}
