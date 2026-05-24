<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'email',
        'password',
        'is_super_admin',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_super_admin'    => 'boolean',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    /** Virtual role: super admins get 'super_admin', others get stored role or 'user'. */
    public function getRoleAttribute($value): string
    {
        if ($this->is_super_admin) {
            return 'super_admin';
        }
        return $value ?? 'user';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function incidentDocuments(): HasMany
    {
        return $this->hasMany(IncidentDocument::class, 'uploaded_by');
    }

    public function inspectionDocuments(): HasMany
    {
        return $this->hasMany(InspectionDocument::class, 'uploaded_by');
    }

    public function complianceForms(): HasMany
    {
        return $this->hasMany(ComplianceForm::class, 'generated_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(\App\Models\AuditLog::class, 'user_id');
    }
}
