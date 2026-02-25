<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientCheckIn extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'patient_check_ins';

    protected $hidden = ['id', 'patient_id', 'doctor_id', 'room_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_doctor_id', 'x_room_id', 'x_company_id'];

    protected $filterable = ['patient_id', 'doctor_id', 'room_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXRoomIdAttribute' => 'room_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'doctor_id' => Hash::class . ':hash',
        'room_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'check_in_datetime' => 'datetime',
        'check_out_datetime' => 'datetime',
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

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Accessors
    public function getStatusAttribute()
    {
        return $this->check_out_datetime ? 'checked_out' : 'checked_in';
    }

    // Mutators
    public function setCheckOutDatetimeAttribute($value)
    {
        $this->attributes['check_out_datetime'] = $value;
        
        // Calculate duration when checking out
        if ($value && $this->check_in_datetime) {
            $checkIn = \Carbon\Carbon::parse($this->check_in_datetime);
            $checkOut = \Carbon\Carbon::parse($value);
            $this->attributes['duration'] = $checkOut->diffInMinutes($checkIn);
        }
    }
}
