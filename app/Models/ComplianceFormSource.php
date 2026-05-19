<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceFormSource extends Model
{
    protected $fillable = [
        'form_id',
        'source_table',
        'source_type',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(ComplianceFormsMaster::class, 'form_id');
    }
}
