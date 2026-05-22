<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    protected $table = 'contractor_master';
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'contractor_name',
        'license_number',
        'valid_from',
        'valid_to',
        'max_worker_limit',
        'pf_code',
        'esi_code',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            $user = auth('web')->user();
            if ($user && $user->tenant_id) {
                $query->where('tenant_id', $user->tenant_id);
            }
        });

        static::creating(function ($model) {
            $user = auth('web')->user();
            if ($user && !$model->tenant_id) {
                $model->tenant_id = $user->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contractLabour(): HasMany
    {
        return $this->hasMany(ContractLabour::class);
    }
}
