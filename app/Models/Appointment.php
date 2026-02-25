<?php
namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'appointments';

    protected $default = [
        'xid',
        'appointment_date',
        'duration',
        'treatment_details',
        'status',
        'arrive_datetime',
        'checkin_datetime',
        'in_progress_datetime',
        'completed_datetime',
        'checkout_datetime',
        'appointment_notes',
        'reason_visit',
        'x_patient_id',
        'x_doctor_id',
        'x_company_id',
        'x_room_id',
        'x_treatment_type_id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['id', 'patient_id', 'doctor_id', 'company_id', 'room_id', 'treatment_type_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_doctor_id', 'x_company_id', 'x_room_id', 'x_treatment_type_id', 'flow_status', 'start_time', 'end_time'];

    protected $filterable = ['status', 'appointment_date', 'patient_id', 'doctor_id', 'room_id', 'treatment_type_id', 'reason_visit'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXCompanyIdAttribute' => 'company_id',
        'getXRoomIdAttribute' => 'room_id',
        'getXTreatmentTypeIdAttribute' => 'treatment_type_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'doctor_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'room_id' => Hash::class . ':hash',
        'treatment_type_id' => Hash::class . ':hash',
        'appointment_date' => 'datetime',
        'arrive_datetime' => 'datetime',
        'checkin_datetime' => 'datetime',
        'in_progress_datetime' => 'datetime',
        'completed_datetime' => 'datetime',
        'checkout_datetime' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    /**
     * Prepare a date for array / JSON serialization.
     * Override to return datetime without timezone conversion.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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

    public function treatmentType()
    {
        return $this->belongsTo(TreatmentType::class, 'treatment_type_id');
    }

    public function appointmentItems()
    {
        return $this->hasMany(AppointmentItem::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    public function histories()
    {
        return $this->hasMany(AppointmentHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the flow status derived from datetime fields.
     * This is the source of truth for appointment progression.
     * 
     * Flow: scheduled -> checked_in -> in_progress -> completed -> checked_out
     *
     * @return string
     */
    public function getFlowStatusAttribute(): string
    {
        if ($this->checkout_datetime) {
            return 'checked_out';
        }
        if ($this->completed_datetime) {
            return 'completed';
        }
        if ($this->in_progress_datetime) {
            return 'in_progress';
        }
        if ($this->checkin_datetime || $this->arrive_datetime) {
            return 'checked_in';
        }
        return 'scheduled';
    }

    /**
     * Check if appointment is at a specific flow status.
     *
     * @param string $status
     * @return bool
     */
    public function isAtFlowStatus(string $status): bool
    {
        return $this->flow_status === $status;
    }

    /**
     * Get the next flow status in the progression.
     *
     * @return string|null Returns null if at final status (checked_out)
     */
    public function getNextFlowStatus(): ?string
    {
        return match ($this->flow_status) {
            'scheduled' => 'checked_in',
            'checked_in' => 'in_progress',
            'in_progress' => 'completed',
            'completed' => 'checked_out',
            'checked_out' => null,
            default => 'checked_in',
        };
    }

    public function getStartTimeAttribute()
    {
        return $this->appointment_date ? $this->appointment_date->format('H:i') : null;
    }

    public function getEndTimeAttribute()
    {
        if (!$this->appointment_date) {
            return null;
        }
        $duration = $this->duration ?? 30; // Default 30 mins
        return $this->appointment_date->copy()->addMinutes($duration)->format('H:i');
    }
}
