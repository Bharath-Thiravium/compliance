<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeave extends Model
{
    use SoftDeletes;

    protected $table = 'employee_leave';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'employee_id',
        'leave_from',
        'leave_to',
        'leave_type',
        'days',
        'reason',
        'status',
    ];

    protected $casts = [
        'leave_from' => 'date',
        'leave_to' => 'date',
    ];

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
