<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'subscription_type',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function payrollCycles(): HasMany
    {
        return $this->hasMany(WorkforcePayrollCycle::class);
    }

    public function payrollSetting(): HasOne
    {
        return $this->hasOne(PayrollSetting::class);
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(ContractorMaster::class);
    }

    public function contractLabourDeployments(): HasMany
    {
        return $this->hasMany(ContractLabourDeployment::class);
    }

    public function clraReturns(): HasMany
    {
        return $this->hasMany(ClraReturn::class);
    }

    public function incidentDocuments(): HasMany
    {
        return $this->hasMany(IncidentDocument::class);
    }

    public function inspectionDocuments(): HasMany
    {
        return $this->hasMany(InspectionDocument::class);
    }

    public function complianceForms(): HasMany
    {
        return $this->hasMany(ComplianceForm::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
