<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceAuditLog extends Model
{
    protected $table = 'compliance_form_audit_scores';

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'form_code',
        'audit_score',
        'status',
        'violations',
    ];

    protected $casts = [
        'violations' => 'array',
        'audit_score' => 'integer',
    ];

    public function batch()
    {
        return $this->belongsTo(ComplianceExecutionBatch::class, 'batch_id');
    }
}
