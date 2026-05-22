<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceSection extends Model
{
    protected $table = 'compliance_sections';

    protected $fillable = [
        'section_name',
        'section_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function forms(): HasMany
    {
        return $this->hasMany(ComplianceFormsMaster::class, 'section_id');
    }
}
