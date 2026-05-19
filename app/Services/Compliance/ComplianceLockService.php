<?php

namespace App\Services\Compliance;

use App\Models\ComplianceStatus;
use Exception;

class ComplianceLockService
{
    public function lockAfterGeneration(ComplianceStatus $status): bool
    {
        if ($status->status === 'Generated' || $status->status === 'Uploaded') {
            return $status->update(['status' => 'Locked']);
        }

        return false;
    }

    public function preventEditIfLocked(ComplianceStatus $status): void
    {
        if ($status->isLocked()) {
            throw new Exception('This compliance form is locked and cannot be modified');
        }
    }

    public function unlockForm(ComplianceStatus $status, int $userId): bool
    {
        if (!$this->canUnlock($userId)) {
            throw new Exception('You do not have permission to unlock this form');
        }

        return $status->update(['status' => 'Generated']);
    }

    private function canUnlock(int $userId): bool
    {
        // Placeholder for permission check
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('compliance_manager');
    }
}
