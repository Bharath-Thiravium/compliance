<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardRegister extends Model
{
    use SoftDeletes;

    protected $table = 'hazard_register';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'hazard_date',
        'hazard_type',
        'description',
        'location',
        'severity',
        'status',
        'corrective_action',
        'action_date',
    ];

    protected $casts = [
        'hazard_date' => 'date',
        'action_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
