<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Services\Compliance\BatchInspectionPackService;
use App\Services\Compliance\InspectionPackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class InspectionPackController extends Controller
{
    public function __construct(
        private BatchInspectionPackService $packService,
        private InspectionPackService $mergedPackService,
    ) {}

    /**
     * Generate a merged PDF pack (automated + manual) for a given batch_id.
     */
    public function createMerged(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|integer|exists:compliance_execution_batches,id',
        ]);

        try {
            $result = $this->mergedPackService->generateMergedPack($validated['batch_id']);

            return response()->json([
                'success'          => true,
                'message'          => 'Inspection pack generated successfully.',
                'file'             => basename($result['path']),
                'download_url'     => $result['url'],
                'automated_count'  => $result['automated_count'],
                'manual_count'     => $result['manual_count'],
                'total_count'      => $result['total_count'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'form_codes' => 'nullable|array',
        ]);

        try {
            $zipPath = $this->packService->createInspectionPack(
                $validated['tenant_id'],
                $validated['branch_id'],
                $validated['month'],
                $validated['year'],
                $validated['form_codes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Inspection pack created successfully',
                'file' => basename($zipPath),
                'download_url' => route('compliance.download-pack', ['file' => basename($zipPath)]),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function download(Request $request)
    {
        $fileName = $request->query('file');

        try {
            $filePath = $this->packService->downloadPack($fileName);
            return response()->download($filePath, $fileName);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function downloadMerged(Request $request)
    {
        $fileName = $request->query('file');
        $filePath = Storage::disk('public')->path('inspection_packs/' . $fileName);

        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'File not found.'], 404);
        }

        return response()->download($filePath, $fileName);
    }

    public function list(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer',
            'branch_id' => 'required|integer',
        ]);

        try {
            $packs = $this->packService->getInspectionPackList(
                $validated['tenant_id'],
                $validated['branch_id']
            );

            return response()->json(['success' => true, 'packs' => $packs, 'count' => count($packs)]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
