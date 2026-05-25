<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps to the existing compliance_audit_logs table.
 * Provides virtual accessors so the Super Admin views work
 * without altering the underlying schema.
 */
class AuditLog extends Model
{
    protected $table = 'compliance_audit_logs';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'tenant_id', 'user_id', 'action', 'form_code',
        'batch_id', 'ip_address', 'user_agent', 'metadata', 'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // ── Virtual accessors so views can use action_type / status / etc. ──

    public function getActionTypeAttribute(): string
    {
        return $this->action ?? '';
    }

    public function getActionLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->action ?? ''));
    }

    public function getStatusAttribute(): string
    {
        return ($this->metadata['status'] ?? 'success');
    }

    public function getSectionNameAttribute(): ?string
    {
        return $this->metadata['section_name'] ?? null;
    }

    public function getRequestUrlAttribute(): ?string
    {
        return $this->metadata['request_url'] ?? null;
    }

    public function getRouteNameAttribute(): ?string
    {
        return $this->metadata['route_name'] ?? null;
    }

    public function getErrorMessageAttribute(): ?string
    {
        return $this->metadata['error_message'] ?? null;
    }

    public function getOldValuesAttribute(): ?array
    {
        return $this->metadata['old_values'] ?? null;
    }

    public function getNewValuesAttribute(): ?array
    {
        return $this->metadata['new_values'] ?? null;
    }

    public function getMetaAttribute(): ?array
    {
        return $this->metadata;
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function batch()
    {
        return $this->belongsTo(ComplianceExecutionBatch::class, 'batch_id');
    }
}
