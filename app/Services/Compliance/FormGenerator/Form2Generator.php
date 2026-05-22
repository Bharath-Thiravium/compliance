<?php

namespace App\Services\Compliance\FormGenerator;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class Form2Generator extends BaseFormGenerator
{
    protected string $formCode = 'FORM_2';
    protected string $view = 'compliance.forms.form_2';

    public function generatePdf(array $formData): string
    {
        try {
            $pdf = Pdf::loadView($this->view, $formData)
                ->setPaper('A4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false)
                ->setOption('dpi', 96)
                ->setOption('defaultFont', 'Arial')
                ->setOption('chroot', [public_path()]);
            return $pdf->output();
        } catch (\Exception $e) {
            Log::error('Form2 PDF generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    protected function prepareData(array $rawData): array
    {
        $rows = [];
        foreach ($rawData['records'] ?? [] as $record) {
            $record = $this->normalizeRecord($record);
            $rows[] = [
                'employee_code' => $record['employee_code'] ?? '',
                'name' => $record['name'] ?? 'N/A',
                'designation' => $record['designation'] ?? 'N/A',
                'leave_type' => $record['leave_type'] ?? 'N/A',
                'leave_days' => $record['leave_days'] ?? 0,
            ];
        }

        $month = $rawData['meta']['month'] ?? 1;
        $year = $rawData['meta']['year'] ?? 2024;
        $tenant = $rawData['tenant'] ?? [];
        $branch = $rawData['branch'] ?? [];

        return [
            'header' => [
                'form_title' => 'FORM 2 - Notice of Periods of Work',
                'period' => $this->formatPeriod($month, $year),
                'branch' => $branch,
                'tenant' => is_array($tenant) ? ($tenant['name'] ?? 'N/A') : $tenant,
                'tenant_details' => $tenant,
                'factory_name' => $branch['name'] ?? 'N/A',
                'place' => $branch['address'] ?? 'N/A',
                'district' => $branch['district'] ?? 'N/A',
                'establishment_name' => $tenant['establishment_name'] ?? 'N/A',
                'owner_name' => $tenant['name'] ?? 'N/A',
                'address' => $branch['address'] ?? 'N/A',
            ],
            'rows' => $rows,
            'totals' => [],
            'is_nil' => count($rows) === 0,
        ];
    }
}
