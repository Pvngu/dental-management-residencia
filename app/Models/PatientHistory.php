<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Models\Patient;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class PatientHistory extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_histories';

    protected $hidden = ['id', 'patient_id', 'user_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_user_id', 'x_company_id'];

    protected $filterable = ['event_type', 'patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'patient_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    // Relations
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    /**
     * Create a patient history entry
     */
    public static function createEntry($patientId, $eventType, $data = null, $userId = null, $referenceable = null)
    {
        $history = new static();
        $history->patient_id = $patientId;
        $history->user_id = $userId ?? (Auth::check() ? Auth::id() : null);
        $history->event_type = $eventType;
        $history->data = $data;
        $history->company_id = 1;

        if ($referenceable) {
            $history->referenceable_type = get_class($referenceable);
            $history->referenceable_id = $referenceable->id;
        }

        $history->save();

        return $history;
    }
}
