<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergencyContact extends BaseModel
{
    use HasFactory;

    protected $table = 'emergency_contacts';

    protected $hidden = ['id', 'patient_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id'];

    protected $default = ['xid', 'name', 'phone', 'relation', 'x_patient_id'];

    protected $filterable = ['patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
