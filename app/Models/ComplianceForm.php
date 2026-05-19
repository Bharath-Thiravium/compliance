<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceForm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'form_code',
        'period_from',
        'period_to',
        'frequency_type',
        'file_path',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'generated_at' => 'datetime',
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

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
