<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractorMaster extends Model
{
    use SoftDeletes;

    protected $table = 'contractor_master';

    protected $fillable = [
        'tenant_id',
        'company_type',
        'company_name',
        'company_address',
        'contact_person',
        'contact_number',
        'email',
        'pan_number',
        'gst_number',
        'status',
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

    public function contractorCompliances(): HasMany
    {
        return $this->hasMany(ContractorCompliance::class, 'contractor_id');
    }
}
