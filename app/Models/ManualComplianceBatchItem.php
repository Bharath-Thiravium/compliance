<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualComplianceBatchItem extends Model
{
    protected $table = 'compliance_manual_batch_items';

    protected $fillable = [
        'batch_id',
        'tenant_id',
        'branch_id',
        'compliance_id',
        'status',
        'compliance_result',
        'document_path',
        'file_size',
        'uploaded_at',
        'uploaded_by',
        'remarks',
    ];

    protected $casts = [
        'uploaded_at'  => 'datetime',
        'file_size'    => 'integer',
        'tenant_id'    => 'integer',
        'branch_id'    => 'integer',
        'batch_id'     => 'integer',
        'uploaded_by'  => 'integer',
    ];

    // Valid transitions: [from => [allowed to...]]
    public const TRANSITIONS = [
        'pending'   => ['completed', 'skipped'],
        'skipped'   => ['completed'],
        'completed' => ['skipped', 'completed'], // completed→completed = re-upload
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function batch()
    {
        return $this->belongsTo(ComplianceExecutionBatch::class, 'batch_id');
    }

    public function compliance()
    {
        return $this->belongsTo(ManualComplianceMaster::class, 'compliance_id');
    }
}
