<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceBatchForm extends Model
{
    protected $table = 'compliance_batch_forms';

    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'form_code',
        'section',
        'file_path',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(ComplianceExecutionBatch::class, 'batch_id');
    }

    /**
     * Check if form is pending (not yet generated)
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' || strpos($this->file_path, 'pending') !== false;
    }

    /**
     * Check if form is generated
     */
    public function isGenerated(): bool
    {
        return $this->status === 'success' && !$this->isPending();
    }

    /**
     * Update file path after generation
     */
    public function updateFilePath(string $filePath, string $status = 'success'): void
    {
        $this->update([
            'file_path' => $filePath,
            'status' => $status,
        ]);
    }
}
