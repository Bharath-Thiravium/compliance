<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceTimeline extends Model
{
    protected $fillable = [
        'tenant_id',
        'form_master_id',
        'period_month',
        'period_year',
        'due_date',
        'status',
        'reminder_sent',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_sent' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formMaster(): BelongsTo
    {
        return $this->belongsTo(ComplianceFormsMaster::class, 'form_master_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }
}
