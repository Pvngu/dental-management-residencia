<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'prescriptions';

    protected $guarded = ['id'];

    protected $hidden = ['id', 'patient_id', 'doctor_id', 'appointment_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_doctor_id', 'x_appointment_id', 'x_company_id'];

    protected $filterable = ['status', 'patient_id', 'doctor_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXAppointmentIdAttribute' => 'appointment_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'doctor_id' => Hash::class . ':hash',
        'appointment_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'prescription_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
        
        static::creating(function ($prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = 'RX-' . str_pad(
                    Prescription::withoutGlobalScope(CompanyScope::class)
                        ->where('company_id', $prescription->company_id)
                        ->count() + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
