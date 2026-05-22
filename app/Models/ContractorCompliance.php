<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractorCompliance extends Model
{
    use SoftDeletes;

    protected $table = 'contractor_compliance';

    protected $fillable = [
        'contractor_id',
        'branch_id',
        'clra_license_number',
        'license_valid_from',
        'license_valid_to',
        'max_worker_limit',
        'pf_code',
        'esi_code',
        'labour_registration_number',
        'last_return_filed',
        'is_compliant',
        'compliance_notes',
    ];

    protected $casts = [
        'license_valid_from' => 'date',
        'license_valid_to' => 'date',
        'last_return_filed' => 'date',
        'is_compliant' => 'boolean',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(ContractorMaster::class, 'contractor_id');
    }

    public function contractLabourDeployments(): HasMany
    {
        return $this->hasMany(ContractLabourDeployment::class, 'contractor_compliance_id');
    }
}
