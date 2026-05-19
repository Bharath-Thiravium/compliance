<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payroll_cycle_id',
        'employee_id',
        'total_days_worked',
        'paid_leave_days',
        'unpaid_leave_days',
        'overtime_hours',
        'basic_earned',
        'da_earned',
        'hra_earned',
        'other_allowances',
        'overtime_wages',
        'gross_salary',
        'pf_employee',
        'esi_employee',
        'professional_tax',
        'fines',
        'advances',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'payment_date',
        'payment_mode',
        'transaction_reference',
    ];

    protected $casts = [
        'overtime_hours' => 'decimal:2',
        'basic_earned' => 'decimal:2',
        'da_earned' => 'decimal:2',
        'hra_earned' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'overtime_wages' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'pf_employee' => 'decimal:2',
        'esi_employee' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'fines' => 'decimal:2',
        'advances' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function payrollCycle(): BelongsTo
    {
        return $this->belongsTo(PayrollCycle::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
