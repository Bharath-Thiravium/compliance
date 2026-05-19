<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'pf_rate',
        'esi_rate',
        'ot_multiplier',
        'bonus_min_percent',
        'bonus_max_percent',
    ];

    protected $casts = [
        'pf_rate' => 'decimal:2',
        'esi_rate' => 'decimal:2',
        'ot_multiplier' => 'decimal:2',
        'bonus_min_percent' => 'decimal:2',
        'bonus_max_percent' => 'decimal:2',
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
}
