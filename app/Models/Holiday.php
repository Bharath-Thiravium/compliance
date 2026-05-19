<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'holiday_date',
        'holiday_name',
        'holiday_type',
    ];

    protected $casts = [
        'holiday_date' => 'date',
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
