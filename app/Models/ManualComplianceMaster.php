<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualComplianceMaster extends Model
{
    protected $table = 'compliance_manual_master';

    protected $fillable = [
        'compliance_name',
        'act_name',
        'frequency',
        'due_month',
        'requires_document',
        'is_event_based',
        'is_automatable',
    ];

    protected $casts = [
        'requires_document' => 'boolean',
        'is_event_based'    => 'boolean',
        'is_automatable'    => 'boolean',
    ];
}
