<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorSchedule extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'doctor_schedules';

    protected $hidden = ['id', 'doctor_id', 'company_id'];

    protected $appends = ['xid', 'x_company_id', 'x_doctor_id', 'x_clinic_id'];

    protected $filterable = ['company_id', 'doctor_id', 'clinic_id'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXClinicIdAttribute' => 'clinic_id',
    ];

    protected $casts = [
        'doctor_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function schedule()
    {
        return $this->hasMany(DoctorScheduleDay::class, 'schedule_id');
    }
}
