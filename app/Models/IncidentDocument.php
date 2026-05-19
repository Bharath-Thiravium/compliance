<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'employee_id',
        'incident_type',
        'incident_date',
        'location',
        'description',
        'authority_name',
        'reference_number',
        'document_path',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'uploaded_at' => 'datetime',
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

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
