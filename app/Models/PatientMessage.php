<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientMessage extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_messages';

    protected $fillable = [
        'patient_id',
        'company_id',
        'sent_by_user_id',
        'message',
        'direction',
        'status',
        'phone_number',
        'channel',
        'external_message_id',
        'metadata',
        'sent_at',
        'delivered_at',
        'failed_at',
        'read_at',
        'error_message'
    ];

    protected $hidden = ['id', 'patient_id', 'company_id', 'sent_by_user_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id', 'x_sent_by_user_id'];

    protected $filterable = ['direction', 'status', 'patient_id', 'read_at', 'channel'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXPatientIdAttribute' => 'patient_id',
        'getXSentByUserIdAttribute' => 'sent_by_user_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'patient_id' => Hash::class . ':hash',
        'sent_by_user_id' => Hash::class . ':hash',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'read_at' => 'datetime',
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

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the hashed patient ID (x_patient_id) attribute.
     * Explicit getter to ensure proper encoding for broadcasting.
     */
    public function getXPatientIdAttribute()
    {
        $patientId = $this->getRawOriginal('patient_id') ?? $this->attributes['patient_id'] ?? null;
        if ($patientId) {
            return \Vinkla\Hashids\Facades\Hashids::encode($patientId);
        }
        return null;
    }

    /**
     * Mark the message as read
     */
    public function markAsRead()
    {
        if ($this->direction === 'inbound' && !$this->read_at) {
            $this->read_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Scope to get unread inbound messages
     */
    public function scopeUnread($query)
    {
        return $query->where('direction', 'inbound')
                     ->whereNull('read_at');
    }
}
