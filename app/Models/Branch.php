<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'tenant_id',
        'branch_name',
        'factory_license_number',
        'address',
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

    public function complianceStatuses(): HasMany
    {
        return $this->hasMany(ComplianceStatus::class, 'branch_id');
    }
}
