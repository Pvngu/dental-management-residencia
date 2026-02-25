<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class Doctor extends BaseModel
{
    protected $table = 'doctors';

    protected $hidden = ['id', 'user_id', 'doctor_department_id', 'company_id'];

    protected $appends = ['xid', 'x_user_id', 'x_doctor_department_id', 'x_company_id', 'professional_id_url'];

    protected $filterable = ['doctor_department_id', 'qualification', 'designation', 'specialist'];

    protected $default = ['xid'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXDoctorDepartmentIdAttribute' => 'doctor_department_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'doctor_department_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function specialties()
    {
        return $this->belongsToMany(DoctorSpecialty::class, 'doctor_doctor_specialty');
    }

    public function doctorDepartment()
    {
        return $this->belongsTo(DoctorDepartment::class, 'doctor_department_id');
    }

    public function doctorSchedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function doctorBreaks()
    {
        return $this->hasMany(DoctorBreak::class);
    }

    public function breaks()
    {
        return $this->hasMany(DoctorBreak::class);
    }

    public function doctorHolidays()
    {
        return $this->hasMany(DoctorHoliday::class);
    }

    public function holidays()
    {
        return $this->hasMany(DoctorHoliday::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    public function currentStatus()
    {
        return $this->hasOneThrough(
            UserStatus::class,
            User::class,
            'id', // Foreign key on users table
            'user_id', // Foreign key on user_statuses table
            'user_id', // Local key on doctors table
            'id' // Local key on users table
        )->latest();
    }

    public function getCurrentStatusAttribute()
    {
        $status = $this->currentStatus()->first();
        return $status ? $status->status : 'available';
    }

    public function getProfessionalIdUrlAttribute()
    {
        return $this->professional_id == null ? null : \App\Classes\Common::getFileUrl(null, $this->professional_id);
    }
}
