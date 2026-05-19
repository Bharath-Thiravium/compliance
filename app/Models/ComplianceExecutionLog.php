<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceExecutionLog extends Model
{
    protected $table = 'compliance_execution_logs';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'batch_id',
        'form_code',
        'status',
        'execution_time',
        'records_generated',
        'error_message',
        'execution_mode'
    ];

    protected $casts = [
        'execution_time' => 'integer',
        'records_generated' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function batch()
    {
        return $this->belongsTo(ComplianceExecutionBatch::class, 'batch_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
