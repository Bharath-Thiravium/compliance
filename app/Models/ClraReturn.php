<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClraReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'return_type',
        'period_from',
        'period_to',
        'total_workers',
        'total_wages',
        'total_ot',
        'total_deductions',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'total_wages' => 'decimal:2',
        'total_ot' => 'decimal:2',
        'total_deductions' => 'decimal:2',
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
