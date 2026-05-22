<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFinancialRegister extends Model
{
    use SoftDeletes;

    protected $table = 'employee_financial_register';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'employee_id',
        'transaction_type',
        'amount',
        'transaction_date',
        'reason',
        'status',
        'installments',
        'installment_amount',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'transaction_date' => 'date',
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
