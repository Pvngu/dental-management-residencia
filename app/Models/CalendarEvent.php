<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Company;
use App\Models\ClinicLocation;
use App\Models\Doctor;
use App\Models\Patient;

class CalendarEvent extends BaseModel
{
    protected $fillable = [
        'company_id',
        'clinic_id',
        'doctor_id',
        'patient_id',
        'title',
        'event_date',
        'duration',
        'color',
        'description',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function clinic()
    {
        return $this->belongsTo(ClinicLocation::class, 'clinic_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
