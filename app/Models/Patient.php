<?php

namespace App\Models;

use App\Casts\Hash;
use App\Scopes\CompanyScope;
use App\Models\ClinicLocation;

class Patient extends BaseModel
{
    protected  $table = 'patients';

    protected $hidden = ['id', 'user_id', 'company_id', 'preferred_doctor_id'];

    protected $appends = ['xid', 'x_user_id', 'x_company_id', 'x_preferred_doctor_id', 'unread_messages_count'];

    protected $default = ['xid', 'ssn'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
        'getXPreferredDoctorIdAttribute' => 'preferred_doctor_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'preferred_doctor_id' => Hash::class . ':hash',
        'media_channels' => 'array',
        'dental_chart' => 'array',
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

    public function preferredDoctor()
    {
        return $this->belongsTo(User::class, 'preferred_doctor_id');
    }

    public function patientNotes()
    {
        return $this->hasMany(PatientNote::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function insurances()
    {
        return $this->hasMany(PatientInsurance::class)->orderBy('id', 'asc');
    }

    public function creditCards()
    {
        return $this->hasMany(PatientCreditCard::class);
    }

    public function defaultCreditCard()
    {
        return $this->hasOne(PatientCreditCard::class)->where('is_default', true);
    }

    public function bankAccounts()
    {
        return $this->hasMany(PatientBankAccount::class);
    }

    public function defaultBankAccount()
    {
        return $this->hasOne(PatientBankAccount::class)->where('is_default', true);
    }

    public function paypalAccounts()
    {
        return $this->hasMany(PatientPaypalAccount::class);
    }

    public function defaultPaypalAccount()
    {
        return $this->hasOne(PatientPaypalAccount::class)->where('is_default', true);
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function dentalTreatMonitors()
    {
        return $this->hasMany(DentalTreatMonitor::class);
    }

    public function messages()
    {
        return $this->hasMany(PatientMessage::class);
    }

    public function getUnreadMessagesCountAttribute()
    {
        return $this->hasMany(PatientMessage::class)
                    ->where('direction', 'inbound')
                    ->whereNull('read_at')
                    ->count();
    }

    public function files()
    {
        return $this->hasMany(PatientFile::class, 'patient_id');
    }

    public function homeClinic()
    {
        return $this->belongsTo(ClinicLocation::class, 'home_clinic_id');
    }
}
