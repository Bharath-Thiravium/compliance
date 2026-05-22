<?php

namespace App\Services\Compliance;

use App\Models\ManualComplianceBatchItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ManualComplianceExecutionService
{
    private const DISK = 'public';

    public function complete(ManualComplianceBatchItem $item, UploadedFile $file, int $userId): void
    {
        if (! $item->canTransitionTo('completed')) {
            throw new RuntimeException("Invalid transition from [{$item->status}] to [completed].");
        }

        $disk      = Storage::disk(self::DISK);
        $oldPath   = $item->document_path;
        $newPath   = null;
        $directory = "compliance_documents/{$item->tenant_id}/{$item->batch_id}/{$item->id}";

        // Detect pre-existing DB/storage inconsistency (path in DB but file gone)
        if ($oldPath && ! $disk->exists($oldPath)) {
            Log::warning('ManualCompliance: document_path in DB but file missing on disk.', [
                'item_id' => $item->id,
                'path'    => $oldPath,
            ]);
            // Treat as no old file — do not block the upload
            $oldPath = null;
        }

        // Derive extension from detected MIME type, not user-supplied filename
        $extension = $file->extension() ?: 'bin';
        $fileSize  = $file->getSize();

        try {
            DB::transaction(function () use ($item, $file, $userId, $disk, $directory, $extension, $fileSize, &$newPath) {
                $newPath = $disk->putFileAs($directory, $file, Str::uuid() . '.' . $extension);

                if (! $newPath) {
                    throw new RuntimeException('File storage failed.');
                }

                $item->update([
                    'status'            => 'completed',
                    'compliance_result' => 'compliant',
                    'document_path'     => $newPath,
                    'file_size'         => $fileSize,
                    'uploaded_at'       => now(),
                    'uploaded_by'       => $userId,
                ]);
            });
        } catch (\Throwable $e) {
            // DB rolled back — delete the newly written file to prevent orphan
            if ($newPath && $disk->exists($newPath)) {
                $disk->delete($newPath);
            }
            throw $e;
        }

        // Post-commit: verify the file actually exists on disk
        if (! $disk->exists($newPath)) {
            // DB says file is there but disk disagrees — log and throw so caller knows
            Log::error('ManualCompliance: file missing after commit.', [
                'item_id' => $item->id,
                'path'    => $newPath,
            ]);
            throw new RuntimeException('File was stored but could not be verified on disk. Please retry.');
        }

        // DB committed and file verified — safe to remove the old file
        if ($oldPath && $oldPath !== $newPath && $disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }
    }

    public function skip(ManualComplianceBatchItem $item): void
    {
        if ($item->status === 'skipped') {
            return; // idempotent
        }

        if (! $item->canTransitionTo('skipped')) {
            throw new RuntimeException("Invalid transition from [{$item->status}] to [skipped].");
        }

        $disk    = Storage::disk(self::DISK);
        $oldPath = $item->document_path;

        // Detect inconsistency before clearing — log but do not block
        if ($oldPath && ! $disk->exists($oldPath)) {
            Log::warning('ManualCompliance: skipping item whose document_path points to missing file.', [
                'item_id' => $item->id,
                'path'    => $oldPath,
            ]);
        }

        DB::transaction(function () use ($item) {
            $item->update([
                'status'            => 'skipped',
                'compliance_result' => 'not_applicable',
                'document_path'     => null,
                'file_size'         => null,
                'uploaded_at'       => null,
                'uploaded_by'       => null,
            ]);
        });

        // DB committed — delete file only if it actually exists
        if ($oldPath && $disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }
    }
}
