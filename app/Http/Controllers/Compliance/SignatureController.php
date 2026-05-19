<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Services\Compliance\DigitalSignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SignatureController extends Controller
{
    public function __construct(
        private DigitalSignatureService $signatureService
    ) {}

    public function sign(Request $request, int $batch, string $form)
    {
        try {
            $validated = $request->validate([
                'signatory_name' => 'required|string|max:255',
                'signatory_designation' => 'required|string|max:255',
                'signature_type' => 'required|in:DRAWN,IMAGE,DIGITAL_CERT',
                'signature_data' => 'nullable|string',
            ]);

            $user = Auth::user();
            $tenantId = $user->tenant_id;

            // Get batch details
            $batchModel = DB::table('compliance_execution_batches')
                ->where('id', $batch)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$batchModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found or unauthorized',
                ], 404);
            }

            // Get document path
            $log = DB::table('compliance_generation_logs')
                ->where('batch_id', $batch)
                ->where('form_code', $form)
                ->where('status', 'success')
                ->first();

            if (!$log || !$log->generated_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found. Generate form first.',
                ], 404);
            }

            $branchId = \App\Services\Compliance\ComplianceContextValidator::resolveBranchSafe(
                $tenantId,
                $batchModel->branch_id
            );

            $result = $this->signatureService->signForm(
                $tenantId,
                $branchId,
                $batch,
                $form,
                $validated['signatory_name'],
                $validated['signatory_designation'],
                $validated['signature_type'],
                $validated['signature_data'] ?? null,
                $log->generated_file_path
            );

            return response()->json([
                'success' => true,
                'message' => 'Form signed successfully',
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function verify(int $batch, string $form)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id;

            // Validate batch belongs to tenant
            $batchModel = DB::table('compliance_execution_batches')
                ->where('id', $batch)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$batchModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found or unauthorized',
                ], 404);
            }

            $log = DB::table('compliance_generation_logs')
                ->where('batch_id', $batch)
                ->where('form_code', $form)
                ->first();

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                ], 404);
            }

            $result = $this->signatureService->verifyIntegrity(
                $batch,
                $form,
                $log->generated_file_path
            );

            return response()->json([
                'success' => $result['verified'],
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getDetails(int $batch, string $form)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id;

            // Validate batch belongs to tenant
            $batchModel = DB::table('compliance_execution_batches')
                ->where('id', $batch)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$batchModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found or unauthorized',
                ], 404);
            }

            $signature = $this->signatureService->getSignatureDetails($batch, $form);

            if (!$signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Signature not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'signatory_name' => $signature->signatory_name,
                    'signatory_designation' => $signature->signatory_designation,
                    'signature_type' => $signature->signature_type,
                    'signed_at' => $signature->signed_at,
                    'ip_address' => $signature->ip_address,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function lockBatch(int $batch)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id;

            $this->signatureService->lockBatch($tenantId, $batch);

            return response()->json([
                'success' => true,
                'message' => 'Batch locked successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function unlockBatch(int $batch)
    {
        try {
            $user = Auth::user();
            $tenantId = $user->tenant_id;

            // Only admins can unlock
            if (!$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            $this->signatureService->unlockBatch($tenantId, $batch);

            return response()->json([
                'success' => true,
                'message' => 'Batch unlocked successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
