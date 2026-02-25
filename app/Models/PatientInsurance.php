<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientInsurance extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_insurances';

    protected $hidden = ['id', 'patient_id', 'provider_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_provider_id', 'x_company_id'];

    protected $default = [
        'xid', 
        'patient_id',
        'x_patient_id',
        'provider_id',
        'x_provider_id',
        'company_id',
        'x_company_id',
        'policy_holder_name',
        'relationship_to_holder',
        'member_id',
        'group_number',
        'plan_type',
        'is_primary',
        'verified_at',
        'is_active',
    ];

    protected $filterable = ['is_primary'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXProviderIdAttribute' => 'provider_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'provider_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'provider_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
