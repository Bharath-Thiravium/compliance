<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DigitalSignatureService
{
    public function signForm(
        int $tenantId,
        int $branchId,
        int $batchId,
        string $formCode,
        string $signatoryName,
        string $signatoryDesignation,
        string $signatureType,
        ?string $signatureData,
        string $documentPath
    ): array {
        // Validate context
        ComplianceContextValidator::validate($tenantId, $branchId, 1, 2024);
        
        // Validate batch belongs to tenant
        $batch = DB::table('compliance_execution_batches')
            ->where('id', $batchId)
            ->where('tenant_id', $tenantId)
            ->first();
            
        if (!$batch) {
            throw new \RuntimeException("Batch {$batchId} not found or does not belong to tenant {$tenantId}");
        }

        if ($batch->is_locked) {
            throw new \RuntimeException("Batch {$batchId} is locked and cannot be modified");
        }

        // Check if already signed
        $existing = DB::table('compliance_signatures')
            ->where('batch_id', $batchId)
            ->where('form_code', $formCode)
            ->first();
            
        if ($existing) {
            throw new \RuntimeException("Form {$formCode} in batch {$batchId} is already signed");
        }

        // Compute document hash
        $fullPath = Storage::disk('local')->path($documentPath);
        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Document not found: {$documentPath}");
        }
        
        $documentHash = hash_file('sha256', $fullPath);

        // Process signature
        $signaturePath = null;
        $signatureHash = null;
        
        if ($signatureData) {
            $signaturePath = $this->storeSignature($tenantId, $batchId, $formCode, $signatureType, $signatureData);
            $signatureHash = hash('sha256', $signatureData);
        }

        // Store signature record
        DB::beginTransaction();
        try {
            $signatureId = DB::table('compliance_signatures')->insertGetId([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'form_code' => $formCode,
                'batch_id' => $batchId,
                'signed_by_user_id' => Auth::id(),
                'signatory_name' => $signatoryName,
                'signatory_designation' => $signatoryDesignation,
                'signature_type' => $signatureType,
                'signature_path' => $signaturePath,
                'signature_hash' => $signatureHash ?? '',
                'document_hash' => $documentHash,
                'ip_address' => request()->ip(),
                'signed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Audit log
            $this->logAudit($tenantId, 'FORM_SIGNED', $formCode, $batchId, [
                'signatory_name' => $signatoryName,
                'signature_type' => $signatureType,
            ]);

            DB::commit();

            return [
                'signature_id' => $signatureId,
                'document_hash' => $documentHash,
                'signed_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function verifyIntegrity(int $batchId, string $formCode, string $documentPath): array
    {
        $signature = DB::table('compliance_signatures')
            ->where('batch_id', $batchId)
            ->where('form_code', $formCode)
            ->first();

        if (!$signature) {
            return [
                'verified' => false,
                'error' => 'No signature found',
            ];
        }

        $fullPath = Storage::disk('local')->path($documentPath);
        if (!file_exists($fullPath)) {
            return [
                'verified' => false,
                'error' => 'Document not found',
            ];
        }

        $currentHash = hash_file('sha256', $fullPath);

        if ($currentHash !== $signature->document_hash) {
            $this->logAudit($signature->tenant_id, 'INTEGRITY_VIOLATION', $formCode, $batchId, [
                'expected_hash' => $signature->document_hash,
                'actual_hash' => $currentHash,
            ]);

            return [
                'verified' => false,
                'error' => 'DOCUMENT INTEGRITY VIOLATED',
                'expected_hash' => $signature->document_hash,
                'actual_hash' => $currentHash,
            ];
        }

        return [
            'verified' => true,
            'signed_by' => $signature->signatory_name,
            'signed_at' => $signature->signed_at,
            'document_hash' => $signature->document_hash,
        ];
    }

    public function lockBatch(int $tenantId, int $batchId): void
    {
        $batch = DB::table('compliance_execution_batches')
            ->where('id', $batchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$batch) {
            throw new \RuntimeException("Batch {$batchId} not found");
        }

        DB::table('compliance_execution_batches')
            ->where('id', $batchId)
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by_user_id' => Auth::id(),
            ]);

        $this->logAudit($tenantId, 'BATCH_LOCKED', null, $batchId);
    }

    public function unlockBatch(int $tenantId, int $batchId): void
    {
        DB::table('compliance_execution_batches')
            ->where('id', $batchId)
            ->where('tenant_id', $tenantId)
            ->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by_user_id' => null,
            ]);

        $this->logAudit($tenantId, 'BATCH_UNLOCKED', null, $batchId);
    }

    private function storeSignature(int $tenantId, int $batchId, string $formCode, string $type, string $data): string
    {
        $directory = "compliance/signatures/{$tenantId}/{$batchId}";
        
        if ($type === 'DRAWN') {
            // Base64 image
            $image = str_replace('data:image/png;base64,', '', $data);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            
            $filename = "{$formCode}_" . time() . ".png";
            $path = "{$directory}/{$filename}";
            
            Storage::disk('local')->put($path, $imageData);
            return $path;
        }

        if ($type === 'IMAGE') {
            // Already uploaded file path
            return $data;
        }

        return '';
    }

    private function logAudit(int $tenantId, string $action, ?string $formCode, ?int $batchId, array $metadata = []): void
    {
        DB::table('compliance_audit_logs')->insert([
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'action' => $action,
            'form_code' => $formCode,
            'batch_id' => $batchId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode($metadata),
            'created_at' => now(),
        ]);
    }

    public function getSignatureDetails(int $batchId, string $formCode): ?object
    {
        return DB::table('compliance_signatures')
            ->where('batch_id', $batchId)
            ->where('form_code', $formCode)
            ->first();
    }

    public function getCompanySignature(int $tenantId): ?string
    {
        $signature = DB::table('compliance_signatures')
            ->where('tenant_id', $tenantId)
            ->where('form_code', 'COMPANY_MASTER')
            ->whereNull('batch_id')
            ->orderBy('signed_at', 'desc')
            ->first();

        return $signature ? $signature->signature_data : null;
    }

    public function getBatchSignature(int $tenantId, int $batchId): ?array
    {
        $signature = DB::table('compliance_signatures')
            ->where('tenant_id', $tenantId)
            ->where('batch_id', $batchId)
            ->orderBy('signed_at', 'desc')
            ->first();

        if (!$signature) {
            return null;
        }

        return [
            'signature_path' => $signature->signature_path,
            'signatory_name' => $signature->signatory_name,
            'signatory_designation' => $signature->signatory_designation,
        ];
    }
}
