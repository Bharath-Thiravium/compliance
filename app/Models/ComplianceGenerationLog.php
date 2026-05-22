<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceGenerationLog extends Model
{
    protected $table = 'compliance_generation_logs';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'form_id',
        'compliance_status_id',
        'generated_by',
        'file_path',
        'checksum_hash',
        'generated_snapshot',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'generated_snapshot' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = now();
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(ComplianceFormsMaster::class, 'form_id');
    }

    public function complianceStatus(): BelongsTo
    {
        return $this->belongsTo(ComplianceStatus::class, 'compliance_status_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
