<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceAttachment extends Model
{
    protected $table = 'compliance_attachments';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'form_id',
        'compliance_status_id',
        'uploaded_by',
        'file_path',
        'reference_number',
        'remarks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
