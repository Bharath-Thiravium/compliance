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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

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
}
