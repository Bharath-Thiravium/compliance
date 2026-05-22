<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutoryManualData extends Model
{
    protected $table = 'statutory_manual_data';

    protected $fillable = [
        'tenant_id',
        'month',
        'year',
        'establishment_details',
        'employer_details',
        'employee_summary',
        'wage_summary',
        'attendance_summary',
        'accident_details',
        'contractor_summary',
    ];

    protected $casts = [
        'establishment_details' => 'array',
        'employer_details' => 'array',
        'employee_summary' => 'array',
        'wage_summary' => 'array',
        'attendance_summary' => 'array',
        'accident_details' => 'array',
        'contractor_summary' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
