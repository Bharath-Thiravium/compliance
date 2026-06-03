<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Services\Compliance\SmartSupplementaryTemplateGenerator;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SmartSupplementaryTemplateController extends Controller
{
    public function download(Request $request, string $type)
    {
        $validTypes = ['bonus', 'fines', 'advances', 'deductions', 'incidents', 'hazard_register', 'contractors'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid template type'], 400);
        }

        $user = auth()->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $branchId = (int) ($request->query('branch_id') ?? $request->input('branch_id') ?? $user->branch_id ?? 1);

        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');
            
            Log::info('SmartTemplate download started', ['type' => $type, 'branch_id' => $branchId, 'tenant_id' => $user->tenant_id]);
            
            $this->cleanupOldTemplateTempFiles();
            
            $generator = new SmartSupplementaryTemplateGenerator();
            Log::info('Generator created, calling generate()');
            
            $spreadsheet = $generator->generate($type, $user->tenant_id, $branchId);
            
            Log::info('SmartTemplate generation completed, creating writer');
            
            if (!$spreadsheet) {
                Log::warning('Spreadsheet generation returned null');
                return response()->json(['error' => 'Failed to generate template'], 500);
            }

            $filename = "{$type}_smart_template_" . now()->format('Y-m-d_His') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            
            $spreadsheet->getProperties()->setLastModifiedBy('System');
            
            $temp = tempnam(sys_get_temp_dir(), 'xlsx_');
            if (!$temp) {
                Log::error('Failed to create temporary file');
                return response()->json(['error' => 'Temporary file creation failed'], 500);
            }

            Log::info('Temp file created, writing spreadsheet', ['temp' => $temp]);
            $writer->save($temp);
            Log::info('File saved successfully', ['size' => filesize($temp)]);
            
            if (!file_exists($temp) || filesize($temp) === 0) {
                Log::error('Temporary file is empty or missing');
                return response()->json(['error' => 'Template generation produced empty file'], 500);
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer);
            gc_collect_cycles();

            Log::info('Sending file download', ['filename' => $filename]);
            return response()->download($temp, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Throwable $e) {
            Log::critical('CRITICAL TEMPLATE DOWNLOAD FAILURE', [
                'type' => $type,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function cleanupOldTemplateTempFiles(): void
    {
        $threshold = time() - 3600;

        foreach (glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xlsx_*') ?: [] as $file) {
            if (is_file($file) && @filemtime($file) !== false && filemtime($file) < $threshold) {
                @unlink($file);
            }
        }
    }

    private function escapeCsvValue(string $value): string
    {
        if (str_contains($value, '"') || str_contains($value, ',') || str_contains($value, "\n") || str_contains($value, "\r")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }
}
