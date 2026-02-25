<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fax extends BaseModel
{
    use HasFactory;

    protected $table = 'faxes';

    protected $hidden = ['id', 'company_id', 'patient_id', 'insurance_provider_id', 'created_by'];

    protected $appends = ['xid', 'x_company_id', 'x_patient_id', 'x_insurance_provider_id', 'x_created_by'];

    protected $filterable = ['direction', 'status', 'to_number', 'from_number', 'patient_id', 'insurance_provider_id'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXPatientIdAttribute' => 'patient_id',
        'getXInsuranceProviderIdAttribute' => 'insurance_provider_id',
        'getXCreatedByAttribute' => 'created_by',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'patient_id' => Hash::class . ':hash',
        'insurance_provider_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'meta' => 'array',
        'transmitted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function insuranceProvider()
    {
        return $this->belongsTo(InsuranceProvider::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
