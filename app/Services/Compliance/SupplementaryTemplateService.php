<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SupplementaryTemplateService
{
    private SmartSupplementaryTemplateGenerator $smartGenerator;

    public function __construct()
    {
        $this->smartGenerator = new SmartSupplementaryTemplateGenerator();
    }

    /**
     * Get supported template types
     */
    public function supportedTypes(): array
    {
        return ['bonus', 'fines', 'advances', 'deductions', 'incidents', 'hazard_register', 'contractors'];
    }

    /**
     * Generate smart XLSX template with auto-fill capabilities
     */
    public function generateSmartXlsx(string $type, int $tenantId, int $branchId): Spreadsheet
    {
        return $this->smartGenerator->generate($type, $tenantId, $branchId);
    }

    /**
     * Download smart XLSX template
     */
    public function downloadSmartXlsx(string $type, int $tenantId, int $branchId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->generateSmartXlsx($type, $tenantId, $branchId);
        $filename = "{$type}_template_" . now()->format('Y-m-d_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Get template metadata
     */
    public function metadata(string $type): ?array
    {
        $csvService = new CsvTemplateService();
        return $csvService->metadata($type);
    }

    /**
     * Generate CSV template (backward compatible)
     */
    public function generateCsv(string $type): ?string
    {
        $csvService = new CsvTemplateService();
        return $csvService->generate($type);
    }

    /**
     * Download CSV template (backward compatible)
     */
    public function downloadCsv(string $type): ?\Illuminate\Http\Response
    {
        $csvService = new CsvTemplateService();
        return $csvService->downloadResponse($type);
    }
}
