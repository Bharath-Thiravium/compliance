<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceStatus extends Model
{
    protected $table = 'compliance_status';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'form_id',
        'period_from',
        'period_to',
        'status',
        'version_number',
        'is_revised',
        'revised_from_id',
        'revision_reason',
        'generated_at',
        'uploaded_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'generated_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_revised' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model) {
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function revisedFrom(): BelongsTo
    {
        return $this->belongsTo(ComplianceStatus::class, 'revised_from_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ComplianceStatus::class, 'revised_from_id');
    }

    public function generationLogs(): HasMany
    {
        return $this->hasMany(ComplianceGenerationLog::class, 'compliance_status_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplianceAttachment::class, 'compliance_status_id');
    }

    public function isLocked(): bool
    {
        return $this->status === 'Locked';
    }
}
