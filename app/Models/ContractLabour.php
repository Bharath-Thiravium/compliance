<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractLabour extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contractor_id',
        'employee_id',
        'deployment_location',
        'wage_rate',
        'employment_start',
        'employment_end',
    ];

    protected $casts = [
        'wage_rate' => 'decimal:2',
        'employment_start' => 'date',
        'employment_end' => 'date',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
