<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'employee_id',
        'financial_year',
        'bonus_percentage',
        'bonus_amount',
        'payment_date',
        'status',
    ];

    protected $casts = [
        'bonus_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'payment_date' => 'date',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(WorkforceEmployee::class);
    }
}
