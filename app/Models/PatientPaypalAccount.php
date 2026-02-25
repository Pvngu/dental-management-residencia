<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinkla\Hashids\Facades\Hashids;

class PatientPaypalAccount extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_paypal_accounts';

    protected $hidden = ['id', 'patient_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id'];

    protected $filterable = ['patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Accessors
    public function getXPatientIdAttribute()
    {
        return $this->patient_id ? Hashids::encode($this->patient_id) : null;
    }

    public function getXCompanyIdAttribute()
    {
        return $this->company_id ? Hashids::encode($this->company_id) : null;
    }

    // Set as default account for patient
    public function setAsDefault()
    {
        static::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        $this->update(['is_default' => true]);
    }
}
