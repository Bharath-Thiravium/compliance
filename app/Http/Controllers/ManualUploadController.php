<?php

namespace App\Http\Controllers;

use App\Models\ComplianceExecutionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManualUploadController extends Controller
{
    public function processManualUploads(int $batch)
    {
        try {
            $batchModel = ComplianceExecutionBatch::findOrFail($batch);
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if ($batchModel->tenant_id !== $user->tenant_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access to batch'
                ], 403);
            }

            // Check if user has MINIMAL subscription
            $tenant = DB::table('tenants')->where('id', $user->tenant_id)->first();
            if ($tenant->subscription_type !== 'MINIMAL') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Manual upload processing is for MINIMAL subscription only'
                ], 403);
            }

            // Ensure directory exists
            if (!Storage::disk('local')->exists('compliance/manual_uploads')) {
                Storage::disk('local')->makeDirectory('compliance/manual_uploads');
            }

            // Count uploaded files
            $uploadCount = DB::table('compliance_manual_uploads')
                ->where('batch_id', $batch)
                ->count();

            if ($uploadCount === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No manual uploads found for this batch'
                ], 400);
            }

            // Update batch status
            $batchModel->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);

            // Log audit
            DB::table('compliance_audit_logs')->insert([
                'tenant_id' => $user->tenant_id,
                'user_id' => Auth::id(),
                'action' => 'MANUAL_UPLOADS_PROCESSED',
                'batch_id' => $batch,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => json_encode(['upload_count' => $uploadCount]),
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Manual uploads processed successfully! {$uploadCount} files uploaded."
            ]);

        } catch (\Throwable $e) {
            logger()->error('Process Manual Uploads Error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}