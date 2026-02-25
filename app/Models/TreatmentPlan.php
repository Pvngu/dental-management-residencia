<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentPlan extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'treatment_plans';

    protected $hidden = ['id', 'patient_id', 'doctor_id', 'created_by', 'updated_by', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_doctor_id', 'x_created_by', 'x_updated_by', 'x_company_id'];

    protected $filterable = ['status', 'priority', 'tooth_number', 'patient_id', 'doctor_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXUpdatedByAttribute' => 'updated_by',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'doctor_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'estimated_cost' => 'decimal:2',
        'estimated_duration' => 'integer',
        'tooth_number' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
