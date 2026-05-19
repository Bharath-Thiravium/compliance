<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceReminder extends Model
{
    protected $fillable = [
        'tenant_id',
        'form_id',
        'reminder_type',
        'due_date',
        'reminder_sent_at',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_sent_at' => 'datetime',
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
}
